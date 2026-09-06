<?php

namespace App\Observers;

use App\Models\Registration;
use App\Models\User;
use Filament\Notifications\Notification;

/**
 * Notifies every admin (via the panel's bell icon — see PageObserver for the same
 * pattern) when a new PPDB registration arrives from the public frontend form. This
 * is a database notification only: there's no SMTP/mail configuration in this
 * environment to send a real outbound email, so the bell is the substitute.
 */
class RegistrationObserver
{
    public function created(Registration $registration): void
    {
        Notification::make()
            ->title('Pendaftaran PPDB baru')
            ->body("{$registration->child_name} ({$registration->parent_name}) mendaftar untuk unit ".(Registration::UNITS[$registration->unit] ?? $registration->unit).'.')
            ->icon('heroicon-o-clipboard-document-list')
            ->iconColor('success')
            ->sendToDatabase(User::all());
    }
}
