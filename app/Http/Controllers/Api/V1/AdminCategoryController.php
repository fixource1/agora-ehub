<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCategoryRequest;
use App\Http\Requests\Api\V1\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\LookupCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Category::class);

        $query = Category::query()
            ->with('parent')
            ->withCount('resources')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($search) use ($term) {
                $search->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return CategoryResource::collection($query->paginate(50));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        $category = Category::query()->create($data);
        $category->load('parent')->loadCount('resources');

        LookupCache::forgetCategories();

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        $this->authorize('view', $category);
        $category->load('parent')->loadCount('resources');

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $data = $request->validated();

        if (array_key_exists('name', $data) && ! array_key_exists('slug', $data)) {
            $data['slug'] = $this->resolveSlug($category->slug, $data['name'], $category->id);
        } elseif (array_key_exists('slug', $data)) {
            $data['slug'] = $this->resolveSlug($data['slug'], $data['name'] ?? $category->name, $category->id);
        }

        $category->update($data);
        $category->load('parent')->loadCount('resources');

        LookupCache::forgetCategories();

        return new CategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);
        $category->delete();

        LookupCache::forgetCategories();

        return response()->json(['message' => 'Category deleted.']);
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        $candidate = $base;
        $counter = 1;

        while (
            Category::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate ?: Str::random(8);
    }
}
