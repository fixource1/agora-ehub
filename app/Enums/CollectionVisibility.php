<?php

namespace App\Enums;

enum CollectionVisibility: string
{
    case Private = 'private';
    case Public = 'public';
    case Institutional = 'institutional';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::Public => 'Public',
            self::Institutional => 'Institutional',
        };
    }
}
