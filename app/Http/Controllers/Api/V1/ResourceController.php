<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
use App\Http\Resources\ResourceSummaryResource;
use App\Jobs\RecordResourceView;
use App\Models\Resource;
use App\Services\ResourceCatalogQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ResourceController extends Controller
{
    public function __construct(private ResourceCatalogQuery $catalog) {}

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $perPage = $this->catalog->perPage($request);

        if ($this->shouldCacheCatalogIndex($request)) {
            $cacheKey = $this->catalogCacheKey($request, $perPage);
            $ttl = (int) config('agora.performance.catalog_cache_seconds', 120);

            $json = Cache::remember($cacheKey, now()->addSeconds($ttl), function () use ($request, $perPage) {
                $paginator = $this->catalog->listQuery($request)->paginate($perPage);

                return ResourceSummaryResource::collection($paginator)->response()->getContent();
            });

            return response($json, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'public, max-age='.$ttl,
            ]);
        }

        $query = $this->catalog->listQuery($request);

        if ($request->filled('cursor')) {
            return ResourceSummaryResource::collection(
                $query->cursorPaginate($perPage)->withQueryString(),
            );
        }

        return ResourceSummaryResource::collection(
            $query->paginate($perPage)->withQueryString(),
        );
    }

    private function shouldCacheCatalogIndex(Request $request): bool
    {
        return ! $request->filled('cursor')
            && ! $request->filled('q')
            && ! $request->filled('page');
    }

    private function catalogCacheKey(Request $request, int $perPage): string
    {
        $type = $request->string('type')->toString() ?: '-';
        $category = $request->string('category')->toString() ?: '-';

        return "agora:catalog:index:v3:{$perPage}:{$type}:{$category}";
    }

    public function show(Request $request, Resource $resource): ResourceResource|JsonResponse
    {
        abort_unless($resource->status === 'published', 404);

        $cacheKey = "agora:resource:show:v3:{$resource->slug}:{$resource->updated_at?->timestamp}";
        $ttl = (int) config('agora.performance.resource_show_cache_seconds', 600);

        $json = Cache::remember($cacheKey, now()->addSeconds($ttl), function () use ($resource) {
            $loaded = $resource->load([
                'resourceType',
                'category',
                'metadata',
                'authors',
                'tags',
                'files',
            ]);

            return (new ResourceResource($loaded))->response()->getContent();
        });

        if (! $request->header('X-Load-Test')) {
            RecordResourceView::dispatch(
                $resource->id,
                $request->user()?->id,
                $request->ip(),
                $request->userAgent(),
            )->afterResponse();
        }

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'public, max-age='.$ttl,
        ]);
    }
}
