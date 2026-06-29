<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mobile remote API (NativePHP)
    |--------------------------------------------------------------------------
    |
    | When enabled on Android/iOS, the Vue app calls this base URL instead of
    | the embedded Laravel API. On WSL2, the emulator often reaches Docker via the
    | WSL IP (e.g. http://172.x.x.x:8000) rather than 10.0.2.2. Run
    | scripts/setup-android-api-access.cmd before building to auto-detect.
    |
    */

    'mobile_api' => [
        'enabled' => (bool) env('MOBILE_API_ENABLED', false),
        'base_url' => env('MOBILE_API_BASE_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance
    |--------------------------------------------------------------------------
    */

    'performance' => [
        'ensure_sample_files_on_download' => (bool) env('AGORA_ENSURE_SAMPLE_FILES_ON_DOWNLOAD', false),
        'benchmark_output' => storage_path('app/performance/baseline.json'),
        'simulate_base_url' => env('AGORA_SIMULATE_BASE_URL'),
        'catalog_cache_seconds' => (int) env('AGORA_CATALOG_CACHE_SECONDS', 120),
        'resource_show_cache_seconds' => (int) env('AGORA_RESOURCE_SHOW_CACHE_SECONDS', 600),
    ],

];
