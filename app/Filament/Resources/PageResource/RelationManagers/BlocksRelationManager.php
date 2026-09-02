<?php

namespace App\Filament\Resources\PageResource\RelationManagers;

use App\Support\BlockDefinitions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'blocks';

    protected static ?string $title = 'Blok Konten';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Tipe Blok')
                    ->options(BlockDefinitions::options())
                    ->required()
                    ->live()
                    ->disabledOn('edit')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_visible')
                    ->label('Tampilkan blok ini')
                    ->default(true)
                    ->columnSpanFull(),
                Forms\Components\Group::make()
                    ->schema(fn (Get $get): array => BlockDefinitions::schemaFor($get('type') ?? ''))
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => BlockDefinitions::options()[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('data.heading')
                    ->label('Judul')
                    ->limit(40)
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Tampil')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Blok'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
