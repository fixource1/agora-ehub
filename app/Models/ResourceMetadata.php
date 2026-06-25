<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceMetadata extends Model
{
    protected $table = 'resource_metadata';

    protected $fillable = [
        'resource_id',
        'publisher',
        'institution',
        'department',
        'publication_date',
        'isbn',
        'doi',
        'citation',
        'research_field',
        'subject_classification',
        'page_count',
        'duration_seconds',
        'custom_fields',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'custom_fields' => 'array',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
