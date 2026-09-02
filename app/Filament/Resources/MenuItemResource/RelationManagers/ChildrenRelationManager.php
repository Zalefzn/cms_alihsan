<?php

namespace App\Filament\Resources\MenuItemResource\RelationManagers;

use App\Filament\Support\TranslatableField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Sub Menu';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...TranslatableField::text('label', 'Teks Menu', required: true),
                Forms\Components\TextInput::make('url')
                    ->label('Link')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('open_in_new_tab')
                    ->label('Buka di tab baru'),
                Forms\Components\Toggle::make('is_visible')
                    ->label('Tampilkan')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Teks Menu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('Link')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Tampil')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Status Tampil')
                    ->trueLabel('Tampil')
                    ->falseLabel('Disembunyikan'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Sub Menu'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
