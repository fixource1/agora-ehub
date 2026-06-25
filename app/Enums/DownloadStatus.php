<?php

namespace App\Enums;

enum DownloadStatus: string
{
    case Pending = 'pending';
    case Downloaded = 'downloaded';
    case Removed = 'removed';
    case PendingSync = 'pending_sync';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Downloaded => 'Downloaded',
            self::Removed => 'Removed',
            self::PendingSync => 'Pending Sync',
        };
    }
}
