<?php

namespace App\Enums;

enum AudienceLevel: string
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
    case Professional = 'professional';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Beginner => 'Beginner',
            self::Intermediate => 'Intermediate',
            self::Advanced => 'Advanced',
            self::Professional => 'Professional',
            self::General => 'General',
        };
    }
}
