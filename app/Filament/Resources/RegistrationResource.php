<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * PPDB (student admission) submissions captured from the frontend's registration
 * form block (see RegistrationController) — no create form since rows only ever
 * arrive via that public API; the only admin action is reviewing/moving `status`.
 */
class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Pendaftaran PPDB';

    protected static ?string $modelLabel = 'Pendaftaran PPDB';

    protected static ?string $pluralModelLabel = 'Pendaftaran PPDB';

    protected static ?string $navigationGroup = 'Formulir & Pendaftaran';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('status', 'baru')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('child_name')->label('Nama Anak'),
            TextEntry::make('parent_name')->label('Nama Orang Tua'),
            TextEntry::make('phone')->label('Telepon'),
            TextEntry::make('email')->label('Email')->placeholder('—'),
            TextEntry::make('unit')->label('Unit')
                ->formatStateUsing(fn (?string $state): string => Registration::UNITS[$state] ?? $state),
            TextEntry::make('message')->label('Pesan')->placeholder('—')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('child_name')
                    ->label('Nama Anak')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent_name')
                    ->label('Nama Orang Tua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Registration::UNITS[$state] ?? $state),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->copyable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->placeholder('—'),
                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options(Registration::STATUSES),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dikirim')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Registration::STATUSES),
                Tables\Filters\SelectFilter::make('unit')
                    ->label('Unit')
                    ->options(Registration::UNITS),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ManageRegistrations::route('/'),
        ];
    }
}
