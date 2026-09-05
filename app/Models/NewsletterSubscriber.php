<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Email addresses captured from the frontend footer's "Berlangganan Buletin
 * Sekolah" card (see NewsletterSubscriptionController) — visible/removable
 * from the CMS via App\Filament\Resources\NewsletterSubscriberResource.
 */
class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'locale',
    ];
}
