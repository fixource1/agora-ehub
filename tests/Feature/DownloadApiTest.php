<?php

namespace Tests\Feature;

use App\Models\Download;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Database\Seeders\ResourceTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadApiTest extends TestCase
{
    use RefreshDatabase;

    private function createResource(string $slug = 'sample-resource'): Resource
    {
        $this->seed(ResourceTypeSeeder::class);

        $user = User::factory()->create();
        $type = ResourceType::query()->first();

        return Resource::query()->create([
            'uploader_id' => $user->id,
            'resource_type_id' => $type->id,
            'title' => 'Sample Resource',
            'slug' => $slug,
            'description' => 'Sample description.',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_guest_can_remove_download_for_their_device(): void
    {
        $resource = $this->createResource('sample-resource');
        $deviceId = 'test-device-123';

        Download::query()->create([
            'user_id' => null,
            'resource_id' => $resource->id,
            'status' => 'downloaded',
            'device_id' => $deviceId,
            'device_name' => 'Web',
            'downloaded_at' => now(),
        ]);

        $response = $this
            ->withHeader('X-Device-Id', $deviceId)
            ->deleteJson("/api/v1/downloads/{$resource->slug}");

        $response->assertOk();
        $this->assertDatabaseMissing('downloads', [
            'resource_id' => $resource->id,
            'device_id' => $deviceId,
        ]);
    }

    public function test_guest_download_index_only_returns_their_device_downloads(): void
    {
        $resource = $this->createResource('device-a-resource');
        $otherResource = $this->createResource('device-b-resource');

        Download::query()->create([
            'user_id' => null,
            'resource_id' => $resource->id,
            'status' => 'downloaded',
            'device_id' => 'device-a',
            'device_name' => 'Web',
            'downloaded_at' => now(),
        ]);

        Download::query()->create([
            'user_id' => null,
            'resource_id' => $otherResource->id,
            'status' => 'downloaded',
            'device_id' => 'device-b',
            'device_name' => 'Web',
            'downloaded_at' => now(),
        ]);

        $response = $this
            ->withHeader('X-Device-Id', 'device-a')
            ->getJson('/api/v1/downloads');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.resource.slug', $resource->slug);
    }
}
