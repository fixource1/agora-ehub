<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{
    protected $table = 'reading_progress';

    protected $fillable = [
        'user_id',
        'resource_id',
        'resource_file_id',
        'location',
        'current_page',
        'total_pages',
        'percentage',
        'last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'last_read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function resourceFile(): BelongsTo
    {
        return $this->belongsTo(ResourceFile::class);
    }
}
