<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Read-only browser + delete tool for every file sitting in the public storage
 * disk (everything uploaded via any block/settings FileUpload field lands under
 * `storage/app/public`, symlinked as `public/storage`). There's no separate
 * `media` table tracking uploads — this scans the disk directly, so it can't
 * tell you *where* a file is used, only that it exists and how big it is; the
 * admin is expected to check a block still references a file before deleting it
 * here.
 */
class MediaLibrary extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Pustaka Media';

    protected static ?string $title = 'Pustaka Media';

    protected static ?string $navigationGroup = 'Alat';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.media-library';

    /**
     * @var array<int, array{path: string, url: string, name: string, size: string, isImage: bool, modified: string}>
     */
    public array $files = [];

    public function mount(): void
    {
        $this->loadFiles();
    }

    protected function loadFiles(): void
    {
        $disk = Storage::disk('public');

        $this->files = collect($disk->allFiles())
            ->reject(fn (string $path): bool => Str::startsWith(basename($path), '.'))
            ->map(fn (string $path): array => [
                'path' => $path,
                'url' => $disk->url($path),
                'name' => basename($path),
                'size' => $this->formatBytes($disk->size($path)),
                'isImage' => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true),
                'modified' => date('d M Y H:i', $disk->lastModified($path)),
            ])
            ->sortByDesc(fn (array $file): string => $file['modified'])
            ->values()
            ->all();
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / (1024 * 1024), 1).' MB';
    }

    public function deleteFile(string $path): void
    {
        Storage::disk('public')->delete($path);

        Notification::make()
            ->title('File dihapus')
            ->success()
            ->send();

        $this->loadFiles();
    }
}
