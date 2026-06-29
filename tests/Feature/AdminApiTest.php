<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Support\LookupCache;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_contributor_cannot_access_admin_user_endpoints(): void
    {
        $contributor = User::factory()->create();
        $contributor->assignRole('contributor');

        Sanctum::actingAs($contributor);

        $this->getJson('/api/v1/admin/users')->assertForbidden();
        $this->postJson('/api/v1/admin/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'role' => 'contributor',
        ])->assertForbidden();
    }

    public function test_administrator_can_manage_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $contributor = User::factory()->create([
            'name' => 'Existing Contributor',
            'email' => 'contributor@example.com',
        ]);
        $contributor->assignRole('contributor');

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonFragment(['email' => $contributor->email]);

        $createResponse = $this->postJson('/api/v1/admin/users', [
            'name' => 'Portal Author',
            'email' => 'author@example.com',
            'password' => 'password123',
            'role' => 'contributor',
            'institution' => 'Test University',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.email', 'author@example.com')
            ->assertJsonPath('data.role', 'contributor');

        $createdId = $createResponse->json('data.id');

        $this->putJson("/api/v1/admin/users/{$createdId}", [
            'name' => 'Updated Author',
            'role' => 'administrator',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Author')
            ->assertJsonPath('data.role', 'administrator');

        $this->deleteJson("/api/v1/admin/users/{$createdId}")
            ->assertOk();

        $this->assertSoftDeleted('users', ['id' => $createdId]);
    }

    public function test_administrator_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/v1/admin/users/{$admin->id}")
            ->assertForbidden();
    }

    public function test_contributor_cannot_access_admin_category_endpoints(): void
    {
        $this->seed(CategorySeeder::class);

        $contributor = User::factory()->create();
        $contributor->assignRole('contributor');

        Sanctum::actingAs($contributor);

        $this->getJson('/api/v1/admin/categories')->assertForbidden();
    }

    public function test_administrator_can_manage_categories_and_bust_lookup_cache(): void
    {
        $this->seed(CategorySeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Cache::put(LookupCache::CATEGORIES_KEY, ['cached' => true], 3600);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/categories')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'is_active', 'resources_count'],
                ],
            ]);

        $createResponse = $this->postJson('/api/v1/admin/categories', [
            'name' => 'New Topic',
            'description' => 'A test category',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'New Topic')
            ->assertJsonPath('data.slug', 'new-topic');

        $this->assertNull(Cache::get(LookupCache::CATEGORIES_KEY));

        $categoryId = $createResponse->json('data.id');

        $this->putJson("/api/v1/admin/categories/{$categoryId}", [
            'name' => 'Renamed Topic',
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed Topic')
            ->assertJsonPath('data.is_active', false);

        $this->assertNull(Cache::get(LookupCache::CATEGORIES_KEY));

        $this->deleteJson("/api/v1/admin/categories/{$categoryId}")
            ->assertOk();

        $this->assertSoftDeleted('categories', ['id' => $categoryId]);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $this->seed(CategorySeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $category = Category::query()->first();

        Sanctum::actingAs($admin);

        $this->putJson("/api/v1/admin/categories/{$category->id}", [
            'parent_id' => $category->id,
        ])->assertUnprocessable();
    }
}
