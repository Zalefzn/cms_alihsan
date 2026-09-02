<?php

namespace App\Support;

/**
 * Curated heroicon choices for a page's sidebar icon (see
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
            'heroicon-o-home' => 'Rumah (Beranda)',
            'heroicon-o-information-circle' => 'Info (Tentang)',
            'heroicon-o-flag' => 'Bendera (Visi & Misi)',
            'heroicon-o-academic-cap' => 'Topi Wisuda (Akademik)',
            'heroicon-o-user-group' => 'Grup Orang (Guru & Staff)',
            'heroicon-o-user-plus' => 'Tambah Orang (Pendaftaran)',
            'heroicon-o-photo' => 'Foto (Galeri)',
            'heroicon-o-newspaper' => 'Koran (Berita)',
            'heroicon-o-phone' => 'Telepon (Kontak)',
            'heroicon-o-building-library' => 'Gedung (Fasilitas)',
            'heroicon-o-calendar-days' => 'Kalender (Agenda/Kegiatan)',
            'heroicon-o-star' => 'Bintang (Prestasi)',
            'heroicon-o-chat-bubble-left-right' => 'Percakapan (FAQ/Testimoni)',
            'heroicon-o-document-text' => 'Dokumen (Umum)',
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
            'home' => 'heroicon-o-home',
            'about' => 'heroicon-o-information-circle',
            'visi' => 'heroicon-o-flag',
            'visi-tk' => 'heroicon-o-flag',
            'visi-kober' => 'heroicon-o-flag',
            'akademik-tk' => 'heroicon-o-academic-cap',
            'akademik-kober' => 'heroicon-o-academic-cap',
            'guru' => 'heroicon-o-user-group',
            'penerimaan' => 'heroicon-o-user-plus',
            'gallery' => 'heroicon-o-photo',
            'news' => 'heroicon-o-newspaper',
            'kontak' => 'heroicon-o-phone',
        ];
    }
}
