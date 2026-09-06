<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers\BlocksRelationManager;
use App\Filament\Support\TranslatableField;
use App\Models\Page;
use App\Support\PageIconOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
     * Without this, global search result titles fall back to the generic
     * model label ("Halaman") for every match instead of the page's own
     * title — see Resource::getRecordTitle().
     */
    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Pages get a custom per-record navigation menu (see
     * AdminPanelProvider) instead of the default single resource link,
     * so this resource's own nav item is turned off to avoid a
     * duplicate.
     */
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Enables the topbar's global search box (hidden entirely otherwise — see
     * FilamentManager::isGlobalSearchEnabled(), which needs at least one
     * resource declaring searchable attributes).
     *
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }

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
                            ->placeholder('Contoh: Beranda, Tentang Kami, Kontak')
                            ->prefixIcon('heroicon-o-document-text')
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
                            ->placeholder('Terisi otomatis setelah judul Indonesia diisi')
                            ->hintAction(TranslatableField::translateAction('title', 'title_en')),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (identitas URL)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('contoh: home, about, kontak')
                            ->columnSpanFull()
                            ->helperText('Dipakai untuk memanggil konten via API, contoh: "home", "about", "kontak".'),
                        ...TranslatableField::textarea('meta_description', 'Deskripsi Meta (SEO)', placeholder: 'Ringkasan singkat halaman ini untuk mesin pencari'),
                        Forms\Components\Select::make('icon')
                            ->label('Ikon di Sidebar')
                            ->options(PageIconOptions::options())
                            ->native(false)
                            ->default('lucide-file-text')
                            ->placeholder('Pilih ikon')
                            ->required(),
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(true)
                            ->visible(fn (): bool => auth()->user()->can('publish_page'))
                            ->dehydrateStateUsing(fn (string $operation, bool $state): bool => ($operation === 'create' && ! auth()->user()->can('publish_page'))
                                ? false
                                : $state)
                            ->helperText('Hanya pemegang izin publikasi yang bisa menerbitkan langsung. Tanpa izin ini, gunakan tombol "Ajukan untuk Ditinjau" di tabel Semua Halaman setelah kontennya siap.'),
                        Forms\Components\Placeholder::make('review_status_display')
                            ->label('Status Tinjauan')
                            ->content(fn (?Page $record): string => $record
                                ? (Page::REVIEW_STATUSES[$record->review_status] ?? $record->review_status)
                                : Page::REVIEW_STATUSES['draft'])
                            ->visible(fn (): bool => ! auth()->user()->can('publish_page')),
                        Forms\Components\Placeholder::make('review_note_display')
                            ->label('Catatan Peninjau')
                            ->content(fn (?Page $record): string => $record?->review_note ?: '—')
                            ->visible(fn (?Page $record): bool => ! auth()->user()->can('publish_page')
                                && $record?->review_status === 'rejected'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('icon')
                    ->label('')
                    ->icon(fn (string $state): string => $state),
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
                Tables\Columns\TextColumn::make('review_status')
                    ->label('Status Tinjauan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Page::REVIEW_STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'in_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status Terbit')
                    ->trueLabel('Terbit')
                    ->falseLabel('Belum Terbit'),
                Tables\Filters\SelectFilter::make('review_status')
                    ->label('Status Tinjauan')
                    ->options(Page::REVIEW_STATUSES),
            ])
            ->actions([
                Tables\Actions\Action::make('build')
                    ->label('Desain Halaman')
                    ->icon('heroicon-o-squares-2x2')
                    ->color('primary')
                    ->url(fn (Page $record): string => static::getUrl('build', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->label('Kelola Konten')
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikat')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->visible(fn (): bool => static::canCreate())
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Halaman Baru')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug Halaman Baru')
                            ->required()
                            ->unique('pages', 'slug')
                            ->alphaDash()
                            ->maxLength(255),
                    ])
                    ->fillForm(fn (Page $record): array => [
                        'title' => "Salinan dari {$record->title}",
                        'slug' => self::uniqueDuplicateSlug($record->slug),
                    ])
                    ->action(function (Page $record, array $data): void {
                        $copy = $record->replicate(['slug', 'title']);
                        $copy->title = $data['title'];
                        $copy->slug = $data['slug'];
                        // A duplicate starts hidden so it can be reviewed/adjusted
                        // before going live under its own slug — never auto-published.
                        $copy->is_published = false;
                        $copy->save();

                        foreach ($record->blocks()->orderBy('order')->get() as $block) {
                            $copy->blocks()->create($block->only(['type', 'order', 'is_visible', 'data']));
                        }

                        Notification::make()
                            ->title('Halaman berhasil diduplikat')
                            ->body("\"{$copy->title}\" dibuat dengan ".$copy->blocks()->count().' blok, belum diterbitkan.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('submitForReview')
                    ->label('Ajukan untuk Ditinjau')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (Page $record): bool => ! auth()->user()->can('publish_page')
                        && in_array($record->review_status, ['draft', 'rejected'], true))
                    ->requiresConfirmation()
                    ->modalDescription('Admin akan diberi tahu untuk meninjau halaman ini sebelum diterbitkan.')
                    ->action(function (Page $record): void {
                        $record->update([
                            'review_status' => 'in_review',
                            'submitted_by' => auth()->id(),
                            'submitted_at' => now(),
                            'review_note' => null,
                        ]);

                        Notification::make()
                            ->title('Diajukan untuk ditinjau')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('approveReview')
                    ->label('Setujui & Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Page $record): bool => auth()->user()->can('publish_page')
                        && $record->review_status === 'in_review')
                    ->requiresConfirmation()
                    ->action(function (Page $record): void {
                        $record->update([
                            'review_status' => 'approved',
                            'is_published' => true,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Halaman disetujui dan diterbitkan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('rejectReview')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Page $record): bool => auth()->user()->can('publish_page')
                        && $record->review_status === 'in_review')
                    ->form([
                        Forms\Components\Textarea::make('review_note')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->placeholder('Jelaskan apa yang perlu diperbaiki sebelum diajukan ulang'),
                    ])
                    ->action(function (Page $record, array $data): void {
                        $record->update([
                            'review_status' => 'rejected',
                            'review_note' => $data['review_note'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Halaman ditolak')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalDescription('Seluruh blok konten di dalamnya akan ikut terhapus dan tidak bisa dikembalikan.'),
                ]),
            ])
            ->defaultSort('title');
    }

    /**
     * "{slug}-copy", or "{slug}-copy-2", "{slug}-copy-3", … the first one not
     * already taken — pre-filled into the duplicate form's slug field so the
     * default suggestion doesn't immediately fail the uniqueness check.
     */
    protected static function uniqueDuplicateSlug(string $slug): string
    {
        $candidate = "{$slug}-copy";
        $suffix = 2;

        while (Page::query()->where('slug', $candidate)->exists()) {
            $candidate = "{$slug}-copy-{$suffix}";
            $suffix++;
        }

        return $candidate;
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
            'build' => Pages\BuildPage::route('/{record}/build'),
        ];
    }
}
