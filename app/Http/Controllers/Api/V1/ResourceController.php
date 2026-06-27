<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
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
            $cacheKey = "agora:catalog:index:v2:{$perPage}";

            $payload = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($request, $perPage) {
                $paginator = $this->catalog->published($request)->paginate($perPage);

                return ResourceResource::collection($paginator)->response()->getData(true);
            });

            return response()->json($payload);
        }

        $query = $this->catalog->published($request);

        if ($request->filled('cursor')) {
            return ResourceResource::collection(
                $query->cursorPaginate($perPage)->withQueryString(),
            );
        }

        return ResourceResource::collection(
            $query->paginate($perPage)->withQueryString(),
        );
    }

    private function shouldCacheCatalogIndex(Request $request): bool
    {
        return ! $request->filled('cursor')
            && ! $request->filled('q')
            && ! $request->filled('type')
            && ! $request->filled('category')
            && ! $request->filled('page');
    }

    public function show(Request $request, Resource $resource): ResourceResource|JsonResponse
    {
        abort_unless($resource->status === 'published', 404);

        $cacheKey = "agora:resource:show:v2:{$resource->slug}:{$resource->updated_at?->timestamp}";

        $payload = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($resource) {
            $loaded = $resource->load([
                'resourceType',
                'category',
                'metadata',
                'authors',
                'tags',
                'files',
            ]);

            return (new ResourceResource($loaded))->response()->getData(true);
        });

        if (! $request->header('X-Load-Test')) {
            RecordResourceView::dispatch(
                $resource->id,
                $request->user()?->id,
                $request->ip(),
                $request->userAgent(),
            )->afterResponse();
        }

        return response()->json($payload);
    }
}
