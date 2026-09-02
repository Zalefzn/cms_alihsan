<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers\BlocksRelationManager;
use App\Filament\Support\TranslatableField;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Halaman';

    protected static ?string $modelLabel = 'Halaman';

    protected static ?string $pluralModelLabel = 'Halaman';

    /**
     * Pages get a custom per-record navigation menu (see
     * AdminPanelProvider) instead of the default single resource link,
     * so this resource's own nav item is turned off to avoid a
     * duplicate.
     */
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Halaman')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Halaman')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Forms\Set $set, Forms\Get $get) {
                                if ($operation === 'create') {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }

                                TranslatableField::autoFill($state, 'title_en', $set, $get);
                            }),
                        Forms\Components\TextInput::make('title_en')
                            ->label('Judul Halaman (Inggris)')
                            ->maxLength(255)
                            ->hintAction(TranslatableField::translateAction('title', 'title_en')),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (identitas URL)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Dipakai untuk memanggil konten via API, contoh: "home", "about", "kontak".'),
                        ...TranslatableField::textarea('meta_description', 'Deskripsi Meta (SEO)'),
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('blocks_count')
                    ->label('Jumlah Blok')
                    ->counts('blocks')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Terbit')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status Terbit'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Kelola Konten'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('title');
    }

    public static function getRelations(): array
    {
        return [
            BlocksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
