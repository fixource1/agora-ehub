<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Resource;
use App\Models\ResourceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ResourceCatalogQuery
{
    public function published(Request $request): Builder
    {
        $query = Resource::query()
            ->with(['resourceType', 'category', 'files' => fn ($fileQuery) => $fileQuery->where('is_primary', true)])
            ->where('status', 'published');

        $this->applyTypeFilter($query, $request->string('type')->toString());
        $this->applyCategoryFilter($query, $request->string('category')->toString());
        $this->applySearch($query, $request->string('q')->toString());

        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 24), 1), 50);
    }

    private function applyTypeFilter(Builder $query, string $typeSlug): void
    {
        if ($typeSlug === '') {
            return;
        }

        $typeId = Cache::remember(
            "agora:lookup:resource-type:{$typeSlug}",
            now()->addHour(),
            fn () => ResourceType::query()->where('slug', $typeSlug)->value('id'),
        );

        if ($typeId) {
            $query->where('resource_type_id', $typeId);
        }
    }

    private function applyCategoryFilter(Builder $query, string $categorySlug): void
    {
        if ($categorySlug === '') {
            return;
        }

        $categoryId = Cache::remember(
            "agora:lookup:category:{$categorySlug}",
            now()->addHour(),
            fn () => Category::query()->where('slug', $categorySlug)->value('id'),
        );

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
    }

    private function applySearch(Builder $query, string $term): void
    {
        $term = trim($term);

        if ($term === '') {
            return;
        }

        if ($query->getConnection()->getDriverName() === 'pgsql') {
            $query->whereFullText(['title', 'subtitle', 'description'], $term);

            return;
        }

        $like = '%'.$term.'%';

        $query->where(function (Builder $search) use ($like) {
            $search->where('title', 'like', $like)
                ->orWhere('subtitle', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
