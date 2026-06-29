<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class LookupCache
{
    public const string CATEGORIES_KEY = 'agora:lookups:categories:v2';

    public const string RESOURCE_TYPES_KEY = 'agora:lookups:resource-types:v2';

    public static function forgetCategories(): void
    {
        Cache::forget(self::CATEGORIES_KEY);
    }
}
