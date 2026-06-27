<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="/brand/agora-icon.svg" type="image/svg+xml">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#00563F">
        <title>{{ config('app.name', 'AGORA e-Hub') }} — Author Dashboard</title>
        <script>
            (function () {
                const saved = localStorage.getItem('agora-theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/web.js'])
    </head>
    <body class="antialiased">
        <div id="web-app"></div>
    </body>
</html>
