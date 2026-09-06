<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Email addresses captured from the frontend footer's "Berlangganan Buletin
 * Sekolah" card (see NewsletterSubscriptionController) — visible/removable
 * from the CMS via App\Filament\Resources\NewsletterSubscriberResource.
 */
class NewsletterSubscriber extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['email', 'locale'])
            ->dontLogEmptyChanges()
            ->useLogName('newsletter_subscriber');
    }

    protected $fillable = [
        'email',
        'locale',
    ];
}
