<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResourceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $resources = Resource::query()
            ->with(['resourceType', 'category', 'files' => fn ($query) => $query->where('is_primary', true)])
            ->where('status', 'published')
            ->when($request->string('type')->isNotEmpty(), fn ($query) => $query->whereHas(
                'resourceType',
                fn ($typeQuery) => $typeQuery->where('slug', $request->string('type')),
            ))
            ->when($request->string('category')->isNotEmpty(), fn ($query) => $query->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('slug', $request->string('category')),
            ))
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($search) use ($term) {
                    $search->where('title', 'like', $term)
                        ->orWhere('subtitle', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->latest('published_at')
            ->paginate(24);

        return ResourceResource::collection($resources);
    }

    public function show(Resource $resource): ResourceResource
    {
        abort_unless($resource->status === 'published', 404);

        $resource->load([
            'resourceType',
            'category',
            'metadata',
            'authors',
            'tags',
            'files',
        ]);

        $resource->increment('view_count');

        return new ResourceResource($resource);
    }
}
