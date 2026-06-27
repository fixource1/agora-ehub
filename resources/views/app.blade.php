<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <link rel="icon" href="/brand/agora-icon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/brand/agora-icon.svg">
        <meta name="theme-color" content="#00563F">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'AGORA e-Hub') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased nativephp-safe-area">
        <script>
            window.__AGORA__ = {
                mobileApiEnabled: @json((bool) config('agora.mobile_api.enabled')),
                mobileApiBaseUrl: @json(config('agora.mobile_api.base_url')),
                nativePlatform: @json(env('NATIVEPHP_PLATFORM')),
            };
        </script>
        <div id="app"></div>
    </body>
</html>
