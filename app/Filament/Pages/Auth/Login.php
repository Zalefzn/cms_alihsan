<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    /**
     * Skip Filament's "simple" centered-card layout (it constrains
     * content to a narrow max-width column) so the view below can
     * lay out a full-bleed two-column split instead.
     */
    protected static string $layout = 'filament-panels::components.layout.base';

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

    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        return parent::getAuthenticateFormAction()
            ->icon('heroicon-o-arrow-right-on-rectangle');
    }

    /**
     * Flags a successful login in the session so the dashboard can
     * greet the user with a SweetAlert welcome toast on its next
     * load (see public/js/sweetalert-bridge.js).
     */
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response) {
            session()->flash('just_logged_in', auth()->user()?->name);
        }

        return $response;
    }
}
