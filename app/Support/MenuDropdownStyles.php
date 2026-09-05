<?php

namespace App\Support;

/**
 * Visual styles for a top-level menu item's dropdown submenu, picked per-item from
 * either the "Menu Navbar" table form or its "Editor Design" builder. Consumed by the
 * frontend's Navbar `DropdownPanel` component, which switches layout based on the
 * value stored here — see BlockDefinitions for the equivalent pattern used by page
 * content blocks (a `variant` field with a matching frontend switch).
 */
class MenuDropdownStyles
{
    public static function options(): array
    {
        return [
            'simple' => 'Daftar Sederhana',
            'columns' => 'Grid 2 Kolom',
            'cards' => 'Kartu dengan Ikon',
        ];
    }
}
