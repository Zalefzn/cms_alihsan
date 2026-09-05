<?php

namespace App\Filament\Resources\NewsletterSubscriberResource\Pages;

use App\Filament\Resources\NewsletterSubscriberResource;
use Filament\Resources\Pages\ManageRecords;

/**
 * No "Buat" header action — rows only ever arrive via the public newsletter
 * subscription endpoint, never typed in by an admin (see the resource class).
 */
class ManageNewsletterSubscribers extends ManageRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
