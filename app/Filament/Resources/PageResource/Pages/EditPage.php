<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('build')
                ->label('Buka Editor Desain')
                ->icon('heroicon-o-squares-2x2')
                ->color('primary')
                ->url(fn (): string => PageResource::getUrl('build', ['record' => $this->getRecord()])),
            Actions\DeleteAction::make()
                ->modalDescription('Seluruh blok kontennya akan ikut terhapus dan tidak bisa dikembalikan.'),
        ];
    }
}
