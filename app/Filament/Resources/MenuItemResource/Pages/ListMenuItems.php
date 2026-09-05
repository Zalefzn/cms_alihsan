<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenuItems extends ListRecords
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('build')
                ->label('Editor Design')
                ->icon('heroicon-o-squares-2x2')
                ->color('primary')
                ->url(fn (): string => MenuItemResource::getUrl('build')),
            Actions\CreateAction::make(),
        ];
    }
}
