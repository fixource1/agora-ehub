<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\ResourceTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LookupApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_types_lookup_returns_serializable_arrays(): void
    {
        $this->seed(ResourceTypeSeeder::class);

        $response = $this->getJson('/api/v1/lookups/resource-types');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'icon', 'description'],
            ],
        ]);

        $cached = Cache::get('agora:lookups:resource-types:v2');
        $this->assertIsArray($cached);
        $this->assertNotEmpty($cached);
        $this->assertArrayHasKey('name', $cached[0]);
    }

    public function test_categories_lookup_returns_serializable_arrays(): void
    {
        $this->seed(CategorySeeder::class);

        $response = $this->getJson('/api/v1/lookups/categories');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug'],
            ],
        ]);

        $cached = Cache::get('agora:lookups:categories:v2');
        $this->assertIsArray($cached);
        $this->assertNotEmpty($cached);
        $this->assertArrayHasKey('name', $cached[0]);
    }
}
