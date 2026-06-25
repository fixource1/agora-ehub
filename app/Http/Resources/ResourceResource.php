<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Resource */
class ResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryFile = $this->files->firstWhere('is_primary', true) ?? $this->files->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'language' => $this->language,
            'audience_level' => $this->audience_level,
            'status' => $this->status,
            'view_count' => $this->view_count,
            'download_count' => $this->download_count,
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
            'metadata' => $this->whenLoaded('metadata'),
            'authors' => $this->whenLoaded('authors', fn () => $this->authors->map(fn ($author) => [
                'id' => $author->id,
                'name' => $author->name,
                'role' => $author->pivot->role,
            ])),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'files' => $this->whenLoaded('files', fn () => $this->files->map(fn ($file) => [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'file_type' => $file->file_type,
                'file_size' => $file->file_size,
                'is_primary' => $file->is_primary,
            ])),
            'primary_file' => $primaryFile ? [
                'id' => $primaryFile->id,
                'file_name' => $primaryFile->file_name,
                'file_type' => $primaryFile->file_type,
                'file_size' => $primaryFile->file_size,
            ] : null,
        ];
    }
}
