<?php

namespace App\Observers;

use App\Models\Page;
use App\Models\User;
use Filament\Notifications\Notification;

/**
 * Feeds the admin panel's notification bell with a couple of
 * practical, real events (not a decorative empty inbox): a new page
 * being created, and an existing page being published for the first
 * time. Sent to every admin user except whoever triggered it.
 */
class PageObserver
{
    public function created(Page $page): void
    {
        Notification::make()
            ->title('Halaman baru dibuat')
            ->body("\"{$page->title}\" ditambahkan oleh ".(auth()->user()?->name ?? 'seseorang').'.')
            ->icon('heroicon-o-document-plus')
            ->iconColor('success')
            ->sendToDatabase($this->recipients());
    }

    public function updated(Page $page): void
    {
        if (! $page->wasChanged('is_published')) {
            return;
        }

        Notification::make()
            ->title($page->is_published ? 'Halaman diterbitkan' : 'Halaman disembunyikan')
            ->body("\"{$page->title}\" ".($page->is_published ? 'sekarang tampil di website.' : 'tidak lagi tampil di website.'))
            ->icon($page->is_published ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
            ->iconColor($page->is_published ? 'success' : 'warning')
            ->sendToDatabase($this->recipients());
    }

    private function recipients()
    {
        return User::query()
            ->when(auth()->id(), fn ($query, $userId) => $query->whereKeyNot($userId))
            ->get();
    }
}
