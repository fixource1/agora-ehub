<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'affiliation',
        'orcid',
        'bio',
    ];

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class)
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }
}
