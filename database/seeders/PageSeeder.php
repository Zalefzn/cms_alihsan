<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed the pages that mirror the public website's route structure,
     * each pre-populated with a starter block so the CMS isn't empty
     * on first login.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => 'Beranda',
                'meta_description' => 'Al-Ihsan Islamic School — National & Singapore-Based Curriculum Integrated With Islamic Values.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Pendidikan Terbaik untuk Anak Anda',
                            'subheading' => 'Al-Ihsan Islamic School — Kurikulum Nasional & Singapura yang dipadukan dengan nilai-nilai Islam.',
                            'cta_text' => 'Pendaftaran 2025 Dibuka',
                            'cta_link' => '/penerimaan',
                        ],
                    ],
                    [
                        'type' => 'feature_list',
                        'data' => [
                            'heading' => 'Suasana Ramah untuk Semua Anak',
                            'items' => [],
                        ],
                    ],
                    [
                        'type' => 'faq',
                        'data' => [
                            'heading' => 'Ketahui Lebih Lanjut Tentang Al-Ihsan',
                            'items' => [],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'about',
                'title' => 'Tentang Kami',
                'meta_description' => 'Profil dan sejarah Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'heading' => 'Tentang Al-Ihsan Islamic School',
                            'body' => '<p>Tuliskan profil dan sejarah sekolah di sini.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'visi',
                'title' => 'Visi & Misi — SD',
                'meta_description' => 'Visi dan misi jenjang SD Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => ['heading' => 'Visi & Misi SD', 'body' => '<p>Tuliskan visi dan misi jenjang SD di sini.</p>'],
                    ],
                ],
            ],
            [
                'slug' => 'visi-tk',
                'title' => 'Visi & Misi — TK',
                'meta_description' => 'Visi dan misi jenjang TK Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => ['heading' => 'Visi & Misi TK', 'body' => '<p>Tuliskan visi dan misi jenjang TK di sini.</p>'],
                    ],
                ],
            ],
            [
                'slug' => 'visi-kober',
                'title' => 'Visi & Misi — Kober',
                'meta_description' => 'Visi dan misi jenjang Kober Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => ['heading' => 'Visi & Misi Kober', 'body' => '<p>Tuliskan visi dan misi jenjang Kober di sini.</p>'],
                    ],
                ],
            ],
            [
                'slug' => 'guru',
                'title' => 'Guru & Staff',
                'meta_description' => 'Daftar guru dan staff pengajar Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'team',
                        'data' => ['heading' => 'Guru & Staff Kami', 'items' => []],
                    ],
                ],
            ],
            [
                'slug' => 'penerimaan',
                'title' => 'Penerimaan Siswa Baru',
                'meta_description' => 'Informasi pendaftaran siswa baru Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => ['heading' => 'Penerimaan Siswa Baru', 'body' => '<p>Tuliskan informasi pendaftaran di sini.</p>'],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'heading' => 'Siap Bergabung?',
                            'body' => 'Daftarkan putra-putri Anda sekarang.',
                            'button_text' => 'Daftar Sekarang',
                            'button_link' => '/kontak',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'akademik-tk',
                'title' => 'Akademik — TK',
                'meta_description' => 'Program akademik jenjang TK.',
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => ['heading' => 'Program Akademik TK', 'body' => '<p>Tuliskan program akademik TK di sini.</p>'],
                    ],
                ],
            ],
            [
                'slug' => 'akademik-kober',
                'title' => 'Akademik — Kober',
                'meta_description' => 'Program akademik jenjang Kober.',
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => ['heading' => 'Program Akademik Kober', 'body' => '<p>Tuliskan program akademik Kober di sini.</p>'],
                    ],
                ],
            ],
            [
                'slug' => 'gallery',
                'title' => 'Galeri',
                'meta_description' => 'Galeri foto dan video kegiatan Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'image_gallery',
                        'data' => ['caption' => 'Dokumentasi kegiatan sekolah', 'images' => []],
                    ],
                ],
            ],
            [
                'slug' => 'news',
                'title' => 'Berita',
                'meta_description' => 'Berita dan kegiatan terbaru Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => ['heading' => 'Berita Terbaru', 'body' => '<p>Tuliskan berita/pengumuman di sini.</p>'],
                    ],
                ],
            ],
            [
                'slug' => 'kontak',
                'title' => 'Kontak',
                'meta_description' => 'Hubungi Al-Ihsan Islamic School.',
                'blocks' => [
                    [
                        'type' => 'contact_info',
                        'data' => [
                            'address' => 'Jl. Cisaranten Kulon No.61, Kec. Arcamanik, Kota Bandung, Jawa Barat 40293',
                            'phone' => '+62 813-2097-5696',
                            'email' => 'administrasi@alihsanislamicsch.co.id',
                            'map_embed' => '',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            $blocks = $pageData['blocks'];
            unset($pageData['blocks']);

            $page = Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                $pageData + ['is_published' => true]
            );

            if ($page->blocks()->count() === 0) {
                foreach ($blocks as $order => $block) {
                    $page->blocks()->create([
                        'type' => $block['type'],
                        'order' => $order,
                        'is_visible' => true,
                        'data' => $block['data'],
                    ]);
                }
            }
        }
    }
}
