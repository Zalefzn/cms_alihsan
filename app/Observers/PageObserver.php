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
        if ($page->wasChanged('is_published')) {
            Notification::make()
                ->title($page->is_published ? 'Halaman diterbitkan' : 'Halaman disembunyikan')
                ->body("\"{$page->title}\" ".($page->is_published ? 'sekarang tampil di website.' : 'tidak lagi tampil di website.'))
                ->icon($page->is_published ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                ->iconColor($page->is_published ? 'success' : 'warning')
                ->sendToDatabase($this->recipients());
        }

        if ($page->wasChanged('review_status')) {
            $this->notifyReviewStatusChange($page);
        }
    }

    /**
     * Ties the review workflow (see PageResource's "Ajukan untuk Ditinjau" /
     * "Setujui & Terbitkan" / "Tolak" actions) into the same notification bell:
     * publishers get told when something needs their review, and whoever
     * submitted it gets told the outcome.
     */
    private function notifyReviewStatusChange(Page $page): void
    {
        match ($page->review_status) {
            'in_review' => Notification::make()
                ->title('Halaman menunggu tinjauan')
                ->body("\"{$page->title}\" diajukan oleh ".(auth()->user()?->name ?? 'seseorang').' untuk ditinjau.')
                ->icon('heroicon-o-paper-airplane')
                ->iconColor('warning')
                ->sendToDatabase($this->publishers()),
            'approved' => Notification::make()
                ->title('Halaman disetujui')
                ->body("\"{$page->title}\" disetujui dan diterbitkan.")
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->sendToDatabase($this->submitter($page)),
            'rejected' => Notification::make()
                ->title('Halaman ditolak')
                ->body("\"{$page->title}\" ditolak: {$page->review_note}")
                ->icon('heroicon-o-x-circle')
                ->iconColor('danger')
                ->sendToDatabase($this->submitter($page)),
            default => null,
        };
    }

    private function recipients()
    {
        return User::query()
            ->when(auth()->id(), fn ($query, $userId) => $query->whereKeyNot($userId))
            ->get();
    }

    private function publishers()
    {
        return User::query()
            ->when(auth()->id(), fn ($query, $userId) => $query->whereKeyNot($userId))
            ->get()
            ->filter(fn (User $user): bool => $user->can('publish_page'));
    }

    private function submitter(Page $page)
    {
        $submitter = $page->submittedBy()->first();

        return $submitter ? collect([$submitter]) : collect();
    }
}
