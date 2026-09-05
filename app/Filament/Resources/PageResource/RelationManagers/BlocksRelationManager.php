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
                    ->helperText('Pilih jenis konten yang ingin ditambahkan ke halaman ini.')
                    ->options(BlockDefinitions::options())
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->live()
                    ->disabledOn('edit')
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('type_description')
                    ->label('')
                    ->content(fn (Get $get): ?string => BlockDefinitions::descriptions()[$get('type')] ?? null)
                    ->visible(fn (Get $get): bool => filled($get('type')))
                    ->columnSpanFull(),
                Forms\Components\Radio::make('data.variant')
                    ->label('Varian Tampilan')
                    ->helperText('Gaya layout section ini di halaman web — isi kontennya tetap sama. Bandingkan pratinjaunya di bawah.')
                    ->options(fn (Get $get): array => BlockDefinitions::variantOptions($get('type') ?? ''))
                    ->live()
                    ->visible(fn (Get $get): bool => BlockDefinitions::variantOptions($get('type') ?? '') !== [])
                    ->columnSpanFull(),
                Forms\Components\ViewField::make('block_preview')
                    ->label('Pratinjau Tampilan')
                    ->view('filament.forms.components.block-type-preview')
                    ->viewData(fn (Get $get): array => [
                        'type' => $get('type'),
                        'variant' => $get('data.variant'),
                        'variants' => BlockDefinitions::variantOptions($get('type') ?? ''),
                        'liveData' => $get('data') ?? [],
                    ])
                    ->visible(fn (Get $get): bool => filled($get('type')))
                    ->dehydrated(false)
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
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Tampil')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Blok')
                    ->options(BlockDefinitions::options()),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Status Tampil')
                    ->trueLabel('Tampil')
                    ->falseLabel('Disembunyikan'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Blok')
                    ->slideOver()
                    ->modalWidth('2xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->slideOver()
                    ->modalWidth('2xl'),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('Isi blok akan hilang dari halaman dan tidak bisa dikembalikan.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalDescription('Isi blok-blok ini akan hilang dari halaman dan tidak bisa dikembalikan.'),
                ]),
            ]);
    }
}
