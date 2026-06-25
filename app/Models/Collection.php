<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'name',
        'slug',
        'description',
        'cover_image',
        'visibility',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'collection_resource')
            ->withPivot(['sort_order', 'added_at'])
            ->orderByPivot('sort_order');
    }
}
