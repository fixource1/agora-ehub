<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResourceFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'resource_id',
        'file_name',
        'file_path',
        'disk',
        'mime_type',
        'file_type',
        'file_size',
        'version',
        'is_primary',
        'is_downloadable',
        'download_count',
        'checksum',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_downloadable' => 'boolean',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
