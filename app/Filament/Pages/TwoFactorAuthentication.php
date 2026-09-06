<?php

namespace App\Filament\Pages;

use App\Support\TwoFactorAuthenticator;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

/**
 * Self-service TOTP two-factor authentication management — every logged-in user
 * manages their OWN 2FA here (not gated by a permission/HasPageShield, unlike
 * the admin-only tools under "Alat": security settings for one's own account
 * are a personal concern, not an admin privilege).
 */
class TwoFactorAuthentication extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Keamanan Akun (2FA)';

    protected static ?string $title = 'Keamanan Akun';

    protected static ?string $navigationGroup = 'Pengguna & Peran';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.two-factor-authentication';

    public bool $enabled = false;

    /** True while a secret has been generated but not yet confirmed with a code. */
    public bool $confirming = false;

    public string $confirmationCode = '';

    public bool $showRecoveryCodes = false;

    /** @var array<int, string> */
    public array $recoveryCodesToShow = [];

    public string $disablePassword = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->enabled = $user->hasEnabledTwoFactorAuthentication();
        // A secret exists but was never confirmed (setup abandoned mid-way) — resume
        // the confirmation step instead of silently leaving it in limbo.
        $this->confirming = ! $this->enabled && ! is_null($user->two_factor_secret);
    }

    public function qrSvg(): ?string
    {
        $user = auth()->user();

        if (! $this->confirming || is_null($user->two_factor_secret)) {
            return null;
        }

        return TwoFactorAuthenticator::qrCodeSvg($user, $user->two_factor_secret);
    }

    public function secretForManualEntry(): ?string
    {
        return $this->confirming ? auth()->user()->two_factor_secret : null;
    }

    public function startSetup(): void
    {
        $user = auth()->user();
        $user->two_factor_secret = TwoFactorAuthenticator::generateSecret();
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        $this->confirming = true;
        $this->confirmationCode = '';
    }

    public function cancelSetup(): void
    {
        $user = auth()->user();
        $user->two_factor_secret = null;
        $user->save();

        $this->confirming = false;
        $this->confirmationCode = '';
    }

    public function confirmSetup(): void
    {
        $user = auth()->user();

        if (! TwoFactorAuthenticator::verify($user->two_factor_secret, trim($this->confirmationCode))) {
            Notification::make()
                ->title('Kode tidak valid')
                ->body('Pastikan kode 6 digit dari aplikasi authenticator sudah benar dan belum kedaluwarsa.')
                ->danger()
                ->send();

            return;
        }

        $codes = TwoFactorAuthenticator::generateRecoveryCodes();

        $user->two_factor_confirmed_at = now();
        $user->two_factor_recovery_codes = $codes;
        $user->save();

        $this->enabled = true;
        $this->confirming = false;
        $this->confirmationCode = '';
        $this->recoveryCodesToShow = $codes;
        $this->showRecoveryCodes = true;

        Notification::make()
            ->title('Autentikasi dua faktor aktif')
            ->success()
            ->send();
    }

    public function regenerateRecoveryCodes(): void
    {
        $user = auth()->user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return;
        }

        $codes = TwoFactorAuthenticator::generateRecoveryCodes();
        $user->two_factor_recovery_codes = $codes;
        $user->save();

        $this->recoveryCodesToShow = $codes;
        $this->showRecoveryCodes = true;

        Notification::make()
            ->title('Kode pemulihan baru dibuat')
            ->body('Kode lama sudah tidak berlaku.')
            ->success()
            ->send();
    }

    public function dismissRecoveryCodes(): void
    {
        $this->showRecoveryCodes = false;
        $this->recoveryCodesToShow = [];
    }

    public function disable(): void
    {
        $user = auth()->user();

        if (! Hash::check($this->disablePassword, $user->password)) {
            Notification::make()
                ->title('Kata sandi salah')
                ->danger()
                ->send();

            return;
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $this->enabled = false;
        $this->confirming = false;
        $this->disablePassword = '';

        Notification::make()
            ->title('Autentikasi dua faktor dinonaktifkan')
            ->success()
            ->send();
    }
}
