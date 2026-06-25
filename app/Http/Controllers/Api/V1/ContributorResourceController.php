<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreResourceRequest;
use App\Http\Requests\Api\V1\UpdateResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ContributorResourceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Resource::class);

        $query = Resource::query()
            ->with(['resourceType', 'category', 'tags', 'files'])
            ->latest('updated_at');

        if (! $request->user()->can('manage resources')) {
            $query->where('uploader_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($search) use ($term) {
                $search->where('title', 'like', $term)
                    ->orWhere('subtitle', 'like', $term);
            });
        }

        return ResourceResource::collection($query->paginate(20));
    }

    public function store(StoreResourceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $resource = Resource::create([
            'uploader_id' => $user->id,
            'resource_type_id' => $data['resource_type_id'],
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'subtitle' => $data['subtitle'] ?? null,
            'description' => $data['description'] ?? null,
            'language' => $data['language'] ?? 'en',
            'audience_level' => $data['audience_level'] ?? 'general',
            'status' => $data['status'] ?? 'draft',
            'published_at' => ($data['status'] ?? 'draft') === 'published' ? now() : null,
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $resource->update(['cover_image' => '/storage/'.$path]);
        }

        $this->syncTags($resource, $data['tags'] ?? []);

        $resource->load(['resourceType', 'category', 'tags', 'files']);

        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Resource $resource): ResourceResource
    {
        $this->authorize('view', $resource);

        $resource->load(['resourceType', 'category', 'metadata', 'authors', 'tags', 'files']);

        return new ResourceResource($resource);
    }

    public function update(UpdateResourceRequest $request, Resource $resource): ResourceResource
    {
        $data = $request->validated();

        if (isset($data['title']) && $data['title'] !== $resource->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $resource->id);
        }

        if (($data['status'] ?? $resource->status) === 'published' && ! $resource->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $data['cover_image'] = '/storage/'.$path;
        }

        unset($data['tags']);
        $resource->update($data);

        if ($request->has('tags')) {
            $this->syncTags($resource, $request->input('tags', []));
        }

        $resource->load(['resourceType', 'category', 'tags', 'files']);

        return new ResourceResource($resource);
    }

    public function destroy(Request $request, Resource $resource): JsonResponse
    {
        $this->authorize('delete', $resource);
        $resource->delete();

        return response()->json(['message' => 'Resource deleted.']);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (
            Resource::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug ?: Str::random(8);
    }

    private function syncTags(Resource $resource, array $tags): void
    {
        $tagIds = collect($tags)
            ->filter()
            ->map(function (string $name) {
                $slug = Str::slug($name);

                return Tag::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name],
                )->id;
            })
            ->all();

        $resource->tags()->sync($tagIds);
    }
}
