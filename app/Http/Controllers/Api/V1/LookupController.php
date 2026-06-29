<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ResourceType;
use App\Support\LookupCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LookupController extends Controller
{
    private const string CATEGORIES_CACHE_KEY = LookupCache::CATEGORIES_KEY;

    private const string RESOURCE_TYPES_CACHE_KEY = LookupCache::RESOURCE_TYPES_KEY;

    public function categories(): JsonResponse
    {
        $categories = Cache::remember(self::CATEGORIES_CACHE_KEY, now()->addDay(), function () {
            return Category::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                ->values()
                ->all();
        });

        return response()
            ->json(['data' => $categories])
            ->header('Cache-Control', 'private, no-cache, must-revalidate');
    }

    public function resourceTypes(): JsonResponse
    {
        $types = Cache::remember(self::RESOURCE_TYPES_CACHE_KEY, now()->addDay(), function () {
            return ResourceType::query()
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'icon', 'description'])
                ->map(fn (ResourceType $type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'icon' => $type->icon,
                    'description' => $type->description,
                ])
                ->values()
                ->all();
        });

        return response()
            ->json(['data' => $types])
            ->header('Cache-Control', 'private, no-cache, must-revalidate');
    }
}
