<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResourceType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'allowed_extensions',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'allowed_extensions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }
}
