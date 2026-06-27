<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Resource */
class ResourceSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryFile = $this->files->firstWhere('is_primary', true) ?? $this->files->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'cover_image' => $this->cover_image,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'resource_type' => $this->whenLoaded('resourceType', fn () => [
                'id' => $this->resourceType->id,
                'name' => $this->resourceType->name,
                'slug' => $this->resourceType->slug,
            ]),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'metadata' => $this->whenLoaded('metadata', fn () => $this->metadata ? [
                'duration_seconds' => $this->metadata->duration_seconds,
            ] : null),
            'primary_file' => $primaryFile ? [
                'id' => $primaryFile->id,
                'file_name' => $primaryFile->file_name,
                'file_type' => $primaryFile->file_type,
                'file_size' => $primaryFile->file_size,
            ] : null,
        ];
    }
}
