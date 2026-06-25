<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage users',
            'manage resources',
            'manage categories',
            'manage collections',
            'view analytics',
            'upload resources',
            'edit own resources',
            'browse resources',
            'download resources',
            'manage library',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('administrator');
        $admin->syncPermissions($permissions);

        $contributor = Role::findOrCreate('contributor');
        $contributor->syncPermissions([
            'upload resources',
            'edit own resources',
            'browse resources',
            'download resources',
            'manage library',
            'manage collections',
        ]);

        $reader = Role::findOrCreate('reader');
        $reader->syncPermissions([
            'browse resources',
            'download resources',
            'manage library',
        ]);
    }
}
