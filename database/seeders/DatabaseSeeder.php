<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ResourceTypeSeeder::class,
            CategorySeeder::class,
            SampleResourceSeeder::class,
        ]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@saliksik.local'],
            [
                'name' => 'Domingo Abucay',
                'password' => Hash::make('password'),
                'institution' => 'UPLB OVCRE',
            ],
        );
        $admin->assignRole('administrator');

        $contributor = User::query()->updateOrCreate(
            ['email' => 'author@saliksik.local'],
            [
                'name' => 'Domingo Abucay',
                'password' => Hash::make('password'),
                'institution' => 'UPLB OVCRE',
                'department' => 'Office of the Vice Chancellor for Research and Extension',
            ],
        );
        $contributor->assignRole('contributor');

        $reader = User::query()->firstOrCreate(
            ['email' => 'reader@saliksik.local'],
            [
                'name' => 'Student Reader',
                'password' => Hash::make('password'),
            ],
        );
        $reader->assignRole('reader');
    }
}
