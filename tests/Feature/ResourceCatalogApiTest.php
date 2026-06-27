<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ResourceTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_resources_index_supports_pagination_and_filters(): void
    {
        $this->seed([ResourceTypeSeeder::class, CategorySeeder::class]);

        $user = User::factory()->create();
        $pdfType = ResourceType::query()->where('slug', 'pdf-document')->first();
        $guidelines = Category::query()->where('slug', 'guidelines')->first();

        Resource::query()->create([
            'uploader_id' => $user->id,
            'resource_type_id' => $pdfType->id,
            'category_id' => $guidelines->id,
            'title' => 'Guidelines PDF Alpha',
            'slug' => 'guidelines-pdf-alpha',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Resource::query()->create([
            'uploader_id' => $user->id,
            'resource_type_id' => $pdfType->id,
            'category_id' => $guidelines->id,
            'title' => 'Guidelines PDF Beta',
            'slug' => 'guidelines-pdf-beta',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $videoType = ResourceType::query()->where('slug', 'video')->first();

        Resource::query()->create([
            'uploader_id' => $user->id,
            'resource_type_id' => $videoType->id,
            'category_id' => $guidelines->id,
            'title' => 'Training Video',
            'slug' => 'training-video',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->getJson('/api/v1/resources?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 3);

        $this->getJson('/api/v1/resources?type=pdf-document')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/resources?category=guidelines&per_page=50')
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->getJson('/api/v1/resources?q=Alpha')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'guidelines-pdf-alpha');
    }

    public function test_resources_index_supports_cursor_pagination(): void
    {
        $this->seed(ResourceTypeSeeder::class);

        $user = User::factory()->create();
        $type = ResourceType::query()->first();

        foreach (range(1, 3) as $index) {
            Resource::query()->create([
                'uploader_id' => $user->id,
                'resource_type_id' => $type->id,
                'title' => "Resource {$index}",
                'slug' => "resource-{$index}",
                'status' => 'published',
                'published_at' => now()->subDays($index),
            ]);
        }

        $firstPage = $this->getJson('/api/v1/resources?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $cursor = $firstPage->json('meta.next_cursor');

        $this->assertNotNull($cursor);

        $this->getJson('/api/v1/resources?per_page=2&cursor='.$cursor)
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
