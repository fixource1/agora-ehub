<?php

namespace Database\Seeders;

use App\Models\ResourceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ResourceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'eBook', 'icon' => 'heroicon-o-book-open', 'allowed_extensions' => ['epub', 'pdf']],
            ['name' => 'PDF Document', 'icon' => 'heroicon-o-document-text', 'allowed_extensions' => ['pdf']],
            ['name' => 'Research Paper', 'icon' => 'heroicon-o-academic-cap', 'allowed_extensions' => ['pdf', 'doc', 'docx']],
            ['name' => 'Journal', 'icon' => 'heroicon-o-newspaper', 'allowed_extensions' => ['pdf', 'epub']],
            ['name' => 'Video', 'icon' => 'heroicon-o-video-camera', 'allowed_extensions' => ['mp4', 'mov', 'webm']],
            ['name' => 'Audio', 'icon' => 'heroicon-o-speaker-wave', 'allowed_extensions' => ['mp3', 'm4a', 'wav']],
            ['name' => 'Presentation', 'icon' => 'heroicon-o-presentation-chart-bar', 'allowed_extensions' => ['ppt', 'pptx', 'pdf']],
            ['name' => 'Other', 'icon' => 'heroicon-o-archive-box', 'allowed_extensions' => ['zip', 'txt', 'doc', 'docx']],
        ];

        foreach ($types as $index => $type) {
            ResourceType::query()->updateOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'icon' => $type['icon'],
                    'allowed_extensions' => $type['allowed_extensions'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
