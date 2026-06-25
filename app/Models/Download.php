<?php

namespace App\Models;

use App\Enums\DownloadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Download extends Model
{
    protected $fillable = [
        'user_id',
        'resource_id',
        'resource_file_id',
        'status',
        'device_id',
        'device_name',
        'downloaded_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DownloadStatus::class,
            'downloaded_at' => 'datetime',
            'last_synced_at' => 'datetime',
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
