<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPagesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Halaman Terbaru Diperbarui')
            ->query(
                Page::query()
                    ->withCount('blocks')
                    ->orderByDesc('updated_at')
                    ->limit(5)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul'),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('blocks_count')
                    ->label('Blok')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Terbit')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->dateTimeTooltip('d M Y H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Kelola')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Page $record) => PageResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
