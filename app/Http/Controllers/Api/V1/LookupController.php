<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ResourceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LookupController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('agora:lookups:categories', now()->addDay(), function () {
            return Category::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);
        });

        return response()
            ->json(['data' => $categories])
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function resourceTypes(): JsonResponse
    {
        $types = Cache::remember('agora:lookups:resource-types', now()->addDay(), function () {
            return ResourceType::query()
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'icon', 'description']);
        });

        return response()
            ->json(['data' => $types])
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
