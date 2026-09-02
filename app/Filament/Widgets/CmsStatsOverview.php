<?php

namespace App\Filament\Widgets;

use App\Models\Block;
use App\Models\MenuItem;
use App\Models\Page;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CmsStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Halaman', Page::query()->count())
                ->description(Page::query()->where('is_published', true)->count().' terbit')
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Total Blok Konten', Block::query()->count())
                ->description('di seluruh halaman')
                ->icon('heroicon-o-squares-2x2')
                ->color('success'),

            Stat::make('Menu Navbar', MenuItem::query()->count())
                ->description(MenuItem::query()->whereNull('parent_id')->count().' menu utama')
                ->icon('heroicon-o-bars-3')
                ->color('warning'),
        ];
    }
}
