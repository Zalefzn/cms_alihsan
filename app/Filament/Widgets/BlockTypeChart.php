<?php

namespace App\Filament\Widgets;

use App\Models\Block;
use App\Support\BlockDefinitions;
use Filament\Widgets\ChartWidget as BaseWidget;

class BlockTypeChart extends BaseWidget
{
    protected static ?string $heading = 'Sebaran Tipe Blok';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '260px';

    protected int | string | array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Block::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $labels = $counts->keys()
            ->map(fn (string $type) => BlockDefinitions::options()[$type] ?? $type)
            ->all();

        return [
            'datasets' => [[
                'data' => $counts->values()->all(),
                'backgroundColor' => [
                    '#6366f1', '#22c55e', '#f59e0b', '#ec4899', '#06b6d4',
                    '#8b5cf6', '#ef4444', '#14b8a6', '#f97316', '#84cc16', '#a855f7',
                ],
            ]],
            'labels' => $labels,
        ];
    }
}
