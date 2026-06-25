<?php

namespace App\Enums;

enum ResourceStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending Review',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }
}
