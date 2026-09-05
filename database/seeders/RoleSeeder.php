<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Two roles for the CMS: `super_admin` (full access to everything,
 * including managing users/roles themselves — bypasses permission
 * checks entirely via Filament Shield's super-admin gate) and
 * `editor` (day-to-day content work: pages and their blocks — including
 * the "Desain Halaman" visual builder, gated by the same `update_page`
 * permission — but no navbar-menu changes, no Pengaturan Situs/SEO, no
 * Pelanggan Buletin, no deleting pages, and no user/role management).
 *
 * Whenever a new admin-only resource/page is added (run
 * `php artisan shield:generate --panel=admin --all` first so its
 * permissions exist), leave it out of the `whereIn` list below to keep
 * it off-limits to `editor` — Spatie's permission model is opt-in, so an
 * unlisted permission is denied by default.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        $editor->syncPermissions(Permission::query()
            ->whereIn('name', [
                'view_page',
                'view_any_page',
                'create_page',
                'update_page',
                'reorder_page',
            ])
            ->get());

        $firstUser = User::query()->orderBy('id')->first();

        if ($firstUser && ! $firstUser->hasRole($superAdmin)) {
            $firstUser->assignRole($superAdmin);
        }

        // A ready-to-use editor account for local testing/demoing the `editor` role —
        // change this password before using the CMS anywhere beyond local dev.
        $editorUser = User::query()->firstOrCreate(
            ['email' => 'editor@alihsanislamicsch.co.id'],
            ['name' => 'Editor Konten', 'password' => Hash::make('password')],
        );

        if (! $editorUser->hasRole($editor)) {
            $editorUser->assignRole($editor);
        }
    }
}
