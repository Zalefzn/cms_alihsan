<?php

namespace App\Observers;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Filament\Notifications\Notification;

/**
 * Notifies every admin (via the panel's bell icon — see PageObserver for the same
 * pattern) when a new newsletter subscriber signs up from the public frontend
 * footer. Database notification only — no SMTP/mail configuration in this
 * environment to send a real outbound email, so the bell is the substitute.
 */
class NewsletterSubscriberObserver
{
    public function created(NewsletterSubscriber $subscriber): void
    {
        Notification::make()
            ->title('Pelanggan buletin baru')
            ->body("{$subscriber->email} berlangganan buletin sekolah.")
            ->icon('heroicon-o-envelope-open')
            ->iconColor('success')
            ->sendToDatabase(User::all());
    }
}
