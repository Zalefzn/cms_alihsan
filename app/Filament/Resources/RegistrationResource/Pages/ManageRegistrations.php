<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Resources\Pages\ManageRecords;

/**
 * No "Buat" header action — rows only ever arrive via the public PPDB
 * registration endpoint, never typed in by an admin (see the resource class).
 */
class ManageRegistrations extends ManageRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
