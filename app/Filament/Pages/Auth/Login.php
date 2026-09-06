<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Support\TwoFactorAuthenticator;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action as FilamentAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    /**
     * Skip Filament's "simple" centered-card layout (it constrains
     * content to a narrow max-width column) so the view below can
     * lay out a full-bleed two-column split instead.
     */
    protected static string $layout = 'filament-panels::components.layout.base';

    /**
     * Whether the password step already succeeded and the form now needs the
     * second (TOTP/recovery code) factor — see authenticate() below. Reusing this
     * SAME page/component for both steps (rather than a separate route) means the
     * whole two-factor challenge gets the login page's existing layout, styling,
     * and rate limiting for free.
     */
    public bool $needsTwoFactor = false;

    public bool $useRecoveryCode = false;

    public function mount(): void
    {
        parent::mount();

        if (session()->has('two_factor.user_id')) {
            $this->needsTwoFactor = true;
        }
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->placeholder('nama@alihsanislamicsch.co.id')
            ->prefixIcon('heroicon-o-envelope');
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->placeholder('Masukkan kata sandi Anda')
            ->prefixIcon('heroicon-o-lock-closed');
    }

    protected function getTwoFactorCodeFormComponent(): Component
    {
        return TextInput::make('twoFactorCode')
            ->label('Kode Autentikasi')
            ->helperText('Masukkan 6 digit kode dari aplikasi authenticator Anda.')
            ->placeholder('123456')
            ->prefixIcon('heroicon-o-shield-check')
            ->autofocus()
            ->extraInputAttributes(['inputmode' => 'numeric', 'maxlength' => 6])
            ->visible(fn (): bool => ! $this->useRecoveryCode)
            ->required(fn (): bool => ! $this->useRecoveryCode)
            ->hintAction(
                FormAction::make('useRecoveryCode')
                    ->label('Pakai kode pemulihan')
                    ->link()
                    ->action(fn () => $this->useRecoveryCode = true),
            );
    }

    protected function getRecoveryCodeFormComponent(): Component
    {
        return TextInput::make('recoveryCode')
            ->label('Kode Pemulihan')
            ->helperText('Salah satu kode pemulihan yang Anda simpan saat mengaktifkan 2FA.')
            ->placeholder('XXXXX-XXXXX')
            ->prefixIcon('heroicon-o-key')
            ->autofocus()
            ->visible(fn (): bool => $this->useRecoveryCode)
            ->required(fn (): bool => $this->useRecoveryCode)
            ->hintAction(
                FormAction::make('useTotpCode')
                    ->label('Pakai kode authenticator')
                    ->link()
                    ->action(fn () => $this->useRecoveryCode = false),
            );
    }

    /**
     * @return array<int|string, \Filament\Forms\Form>
     */
    protected function getForms(): array
    {
        if (! $this->needsTwoFactor) {
            return parent::getForms();
        }

        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getTwoFactorCodeFormComponent(),
                        $this->getRecoveryCodeFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getAuthenticateFormAction(): FilamentAction
    {
        return parent::getAuthenticateFormAction()
            ->icon('heroicon-o-arrow-right-on-rectangle');
    }

    protected function getVerifyTwoFactorFormAction(): FilamentAction
    {
        return FilamentAction::make('verifyTwoFactor')
            ->label('Verifikasi')
            // Routed through the same `authenticate` Livewire method (which
            // branches on $needsTwoFactor) rather than a distinct method name,
            // to match the login <form>'s wire:submit="authenticate" binding.
            ->submit('authenticate')
            ->icon('heroicon-o-shield-check');
    }

    protected function getBackToLoginFormAction(): FilamentAction
    {
        return FilamentAction::make('backToLogin')
            ->label('Kembali ke Login')
            ->color('gray')
            ->link()
            ->action('cancelTwoFactor');
    }

    /**
     * @return array<\Filament\Actions\Action|\Filament\Actions\ActionGroup>
     */
    protected function getFormActions(): array
    {
        if (! $this->needsTwoFactor) {
            return parent::getFormActions();
        }

        return [
            $this->getVerifyTwoFactorFormAction(),
            $this->getBackToLoginFormAction(),
        ];
    }

    /**
     * Filament caches the login form's schema the moment Livewire hydrates the
     * request's submitted field values (before authenticate() itself even runs),
     * so flipping $needsTwoFactor mid-request doesn't by itself make the NEXT
     * render() pick up the new (2FA-step) schema — it would keep serving the
     * already-cached one from earlier in this same request. Busting the cache
     * forces it to rebuild from getForms() with the now-current $needsTwoFactor.
     */
    protected function resetCachedForms(): void
    {
        $this->cachedForms = null;
        $this->hasCachedForms = false;
    }

    public function cancelTwoFactor(): void
    {
        session()->forget(['two_factor.user_id', 'two_factor.remember']);

        $this->needsTwoFactor = false;
        $this->useRecoveryCode = false;
        $this->resetCachedForms();

        $this->form->fill();
    }

    /**
     * Flags a successful login in the session so the dashboard can
     * greet the user with a SweetAlert welcome toast on its next
     * load (see public/js/sweetalert-bridge.js).
     *
     * Also the first step of the two-factor flow: on a valid password, a user
     * with 2FA enabled is logged straight back out and handed a session flag
     * instead of a LoginResponse — see verifyTwoFactor() for the second step,
     * which is what actually completes the login.
     */
    public function authenticate(): ?LoginResponse
    {
        if ($this->needsTwoFactor) {
            return $this->verifyTwoFactor();
        }

        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (($user instanceof FilamentUser) && (! $user->canAccessPanel(Filament::getCurrentPanel()))) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        // Regenerate now (while still a valid, just-attempted session) to close
        // any pre-login session-fixation window, then immediately drop the
        // session's authenticated state back out again for the second factor —
        // the guard is only truly "logged in" once verifyTwoFactor() succeeds.
        session()->regenerate();

        if ($user instanceof User && $user->hasEnabledTwoFactorAuthentication()) {
            Filament::auth()->logout();

            session([
                'two_factor.user_id' => $user->getKey(),
                'two_factor.remember' => $data['remember'] ?? false,
            ]);

            $this->needsTwoFactor = true;
            $this->resetCachedForms();

            return null;
        }

        session()->flash('just_logged_in', $user->name);

        return app(LoginResponse::class);
    }

    protected function verifyTwoFactor(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $userId = session('two_factor.user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            $this->cancelTwoFactor();

            throw ValidationException::withMessages([
                'data.twoFactorCode' => 'Sesi login kedaluwarsa, silakan masuk kembali.',
            ]);
        }

        $data = $this->form->getState();

        $valid = $this->useRecoveryCode
            ? $user->consumeRecoveryCode(trim((string) ($data['recoveryCode'] ?? '')))
            : TwoFactorAuthenticator::verify($user->two_factor_secret, trim((string) ($data['twoFactorCode'] ?? '')));

        if (! $valid) {
            throw ValidationException::withMessages([
                $this->useRecoveryCode ? 'data.recoveryCode' : 'data.twoFactorCode' => 'Kode tidak valid.',
            ]);
        }

        $remember = session('two_factor.remember', false);
        session()->forget(['two_factor.user_id', 'two_factor.remember']);

        Filament::auth()->login($user, $remember);
        session()->regenerate();
        session()->flash('just_logged_in', $user->name);

        return app(LoginResponse::class);
    }
}
