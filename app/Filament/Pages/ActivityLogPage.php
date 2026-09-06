<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

/**
 * Read-only viewer for spatie/laravel-activitylog's `activity_log` table — covers both
 * automatic per-model logging (Page/MenuItem/Setting/User/NewsletterSubscriber/
 * Registration, via their LogsActivity trait) and the explicit activity()->log(...)
 * calls added to BuildPage::save()/BuildMenu::save() for their bulk query-builder
 * updates, which bypass Eloquent events entirely.
 */
class ActivityLogPage extends Page implements HasTable
{
    use HasPageShield;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $title = 'Log Aktivitas';

    protected static ?string $navigationGroup = 'Alat';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.activity-log';

    protected function getTableQuery(): Builder
    {
        return Activity::query()->latest('id');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('created_at')
                ->label('Waktu')
                ->dateTime('d M Y H:i')
                ->sortable(),
            Tables\Columns\TextColumn::make('causer.name')
                ->label('Pengguna')
                ->default('Sistem')
                ->searchable(),
            Tables\Columns\TextColumn::make('log_name')
                ->label('Kategori')
                ->badge()
                ->sortable(),
            Tables\Columns\TextColumn::make('description')
                ->label('Deskripsi')
                ->wrap()
                ->searchable(),
            Tables\Columns\TextColumn::make('subject_type')
                ->label('Subjek')
                ->formatStateUsing(fn (?string $state, Activity $record): string => $state
                    ? class_basename($state).($record->subject_id ? ' #'.$record->subject_id : '')
                    : '—'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('log_name')
                ->label('Kategori')
                ->options(fn (): array => Activity::query()
                    ->distinct()
                    ->pluck('log_name', 'log_name')
                    ->filter()
                    ->all()),
        ];
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'id';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }
}
