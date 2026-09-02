<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Seed the navbar menu matching the current React site's structure
     * (app/components/navbar.tsx), so the CMS starts pre-populated.
     */
    public function run(): void
    {
        if (MenuItem::query()->count() > 0) {
            return;
        }

        $menu = [
            ['label' => 'Beranda', 'url' => '/'],
            [
                'label' => 'Tentang',
                'url' => null,
                'children' => [
                    ['label' => 'Tentang Kami', 'url' => '/about'],
                    ['label' => 'Visi & Misi SD', 'url' => '/visi'],
                    ['label' => 'Visi & Misi TK', 'url' => '/visiTk'],
                    ['label' => 'Visi & Misi Kober', 'url' => '/visiKober'],
                    ['label' => 'Guru & Staff', 'url' => '/guru'],
                ],
            ],
            [
                'label' => 'Akademik',
                'url' => null,
                'children' => [
                    ['label' => 'TK', 'url' => '/akademik/tk'],
                    ['label' => 'Kober', 'url' => '/akademik/kober'],
                ],
            ],
            ['label' => 'Penerimaan', 'url' => '/penerimaan'],
            ['label' => 'Galeri', 'url' => '/picture'],
            ['label' => 'Berita', 'url' => '/news'],
            ['label' => 'Kontak', 'url' => '/kontak'],
        ];

        foreach ($menu as $order => $item) {
            $children = $item['children'] ?? [];
            unset($item['children']);

            $parent = MenuItem::create($item + ['order' => $order, 'is_visible' => true]);

            foreach ($children as $childOrder => $child) {
                $parent->children()->create($child + ['order' => $childOrder, 'is_visible' => true]);
            }
        }
    }
}
