<?php

namespace App\Support;

/**
 * Curated Lucide icon choices for a page's sidebar icon (see
 * PageResource's form and AdminPanelProvider::pageNavigationItems()).
 * Kept as a plain option list (label => icon) rather than a full icon
 * picker plugin, since the set of sensible icons for a school site is
 * small and stable.
 */
class PageIconOptions
{
    public static function options(): array
    {
        return [
            'lucide-home' => 'Rumah (Beranda)',
            'lucide-info' => 'Info (Tentang)',
            'lucide-flag' => 'Bendera (Visi & Misi)',
            'lucide-graduation-cap' => 'Topi Wisuda (Akademik)',
            'lucide-users' => 'Grup Orang (Guru & Staff)',
            'lucide-user-plus' => 'Tambah Orang (Pendaftaran)',
            'lucide-image' => 'Foto (Galeri)',
            'lucide-newspaper' => 'Koran (Berita)',
            'lucide-phone' => 'Telepon (Kontak)',
            'lucide-library' => 'Gedung (Fasilitas)',
            'lucide-calendar-days' => 'Kalender (Agenda/Kegiatan)',
            'lucide-star' => 'Bintang (Prestasi)',
            'lucide-message-circle' => 'Percakapan (FAQ/Testimoni)',
            'lucide-file-text' => 'Dokumen (Umum)',
        ];
    }

    /**
     * Slug => icon for the site's existing pages, used by the
     * migration that backfills this column for records created
     * before it existed.
     */
    public static function defaultsBySlug(): array
    {
        return [
            'home' => 'lucide-home',
            'about' => 'lucide-info',
            'visi' => 'lucide-flag',
            'visi-tk' => 'lucide-flag',
            'visi-kober' => 'lucide-flag',
            'akademik-tk' => 'lucide-graduation-cap',
            'akademik-kober' => 'lucide-graduation-cap',
            'guru' => 'lucide-users',
            'penerimaan' => 'lucide-user-plus',
            'gallery' => 'lucide-image',
            'news' => 'lucide-newspaper',
            'kontak' => 'lucide-phone',
        ];
    }

    /** Old heroicon values (pre-Lucide) mapped to their Lucide replacement, for the one-time data migration. */
    public static function legacyHeroiconMap(): array
    {
        return [
            'heroicon-o-home' => 'lucide-home',
            'heroicon-o-information-circle' => 'lucide-info',
            'heroicon-o-flag' => 'lucide-flag',
            'heroicon-o-academic-cap' => 'lucide-graduation-cap',
            'heroicon-o-user-group' => 'lucide-users',
            'heroicon-o-user-plus' => 'lucide-user-plus',
            'heroicon-o-photo' => 'lucide-image',
            'heroicon-o-newspaper' => 'lucide-newspaper',
            'heroicon-o-phone' => 'lucide-phone',
            'heroicon-o-building-library' => 'lucide-library',
            'heroicon-o-calendar-days' => 'lucide-calendar-days',
            'heroicon-o-star' => 'lucide-star',
            'heroicon-o-chat-bubble-left-right' => 'lucide-message-circle',
            'heroicon-o-document-text' => 'lucide-file-text',
        ];
    }
}
