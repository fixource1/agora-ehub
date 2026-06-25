<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bookmark extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'resource_id',
        'title',
        'location',
        'page_number',
        'excerpt',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
