<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Two roles for the CMS: `super_admin` (full access to everything,
 * including managing users/roles themselves — bypasses permission
 * checks entirely via Filament Shield's super-admin gate) and
 * `editor` (day-to-day content work: pages and their blocks, but no
 * navbar-menu changes, no deleting pages, no user/role management).
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
    }
}
