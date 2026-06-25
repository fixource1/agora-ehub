<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uploader_id',
        'resource_type_id',
        'category_id',
        'title',
        'slug',
        'subtitle',
        'description',
        'cover_image',
        'language',
        'audience_level',
        'status',
        'view_count',
        'download_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function metadata(): HasOne
    {
        return $this->hasOne(ResourceMetadata::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class)
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ResourceFile::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_resource')
            ->withPivot(['sort_order', 'added_at']);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function readingProgress(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(Highlight::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ResourceView::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_favorites');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
