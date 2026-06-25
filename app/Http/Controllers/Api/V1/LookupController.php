<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ResourceType;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $categories]);
    }

    public function resourceTypes(): JsonResponse
    {
        $types = ResourceType::query()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'icon', 'description']);

        return response()->json(['data' => $types]);
    }
}
