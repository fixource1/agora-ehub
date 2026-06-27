<?php

namespace App\Jobs;

use App\Models\ResourceView;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordResourceView implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $resourceId,
        public ?int $userId = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}

    public function handle(): void
    {
        ResourceView::query()->create([
            'resource_id' => $this->resourceId,
            'user_id' => $this->userId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'viewed_at' => now(),
        ]);
    }
}
