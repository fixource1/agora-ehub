<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\ResourceFile;
use App\Models\ResourceType;
use App\Models\User;
use Database\Seeders\ResourceTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourceFileDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_download_primary_resource_file(): void
    {
        Storage::fake('local');
        $this->seed(ResourceTypeSeeder::class);

        $user = User::factory()->create();
        $type = ResourceType::query()->first();

        $resource = Resource::query()->create([
            'uploader_id' => $user->id,
            'resource_type_id' => $type->id,
            'title' => 'Sample Resource',
            'slug' => 'sample-resource',
            'description' => 'Sample description.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $file = ResourceFile::query()->create([
            'resource_id' => $resource->id,
            'file_name' => 'sample.pdf',
            'file_path' => 'resources/samples/sample.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_type' => 'pdf',
            'file_size' => 128,
            'is_primary' => true,
            'is_downloadable' => true,
        ]);

        Storage::disk('local')->put('resources/samples/sample.pdf', '%PDF-sample');

        $response = $this->get("/api/v1/resources/{$resource->slug}/files/{$file->id}/download");

        $response->assertOk();
        $response->assertDownload('sample.pdf');
    }

    public function test_download_creates_placeholder_when_sample_file_missing(): void
    {
        Storage::fake('local');
        $this->seed(ResourceTypeSeeder::class);

        $user = User::factory()->create();
        $type = ResourceType::query()->first();

        $resource = Resource::query()->create([
            'uploader_id' => $user->id,
            'resource_type_id' => $type->id,
            'title' => 'Missing File Resource',
            'slug' => 'missing-file-resource',
            'description' => 'Sample description.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $file = ResourceFile::query()->create([
            'resource_id' => $resource->id,
            'file_name' => 'missing.pdf',
            'file_path' => 'resources/samples/missing.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_type' => 'pdf',
            'file_size' => 128,
            'is_primary' => true,
            'is_downloadable' => true,
        ]);

        $response = $this->get("/api/v1/resources/{$resource->slug}/files/{$file->id}/download");

        $response->assertOk();
        $response->assertDownload('missing.pdf');
        Storage::disk('local')->assertExists('resources/samples/missing.pdf');
    }
}
