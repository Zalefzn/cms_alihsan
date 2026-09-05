<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Read-only-ish list of emails captured from the frontend footer's
 * "Berlangganan Buletin Sekolah" card (see NewsletterSubscriptionController)
 * — no create/edit form since rows only ever arrive via that public API.
 */
class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $navigationLabel = 'Pelanggan Buletin';

    protected static ?string $modelLabel = 'Pelanggan Buletin';

    protected static ?string $pluralModelLabel = 'Pelanggan Buletin';

    protected static ?string $navigationGroup = 'Navigasi Website';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('locale')
                    ->label('Bahasa')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'en' => 'Inggris',
                        default => 'Indonesia',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Berlangganan Sejak')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNewsletterSubscribers::route('/'),
        ];
    }
}
