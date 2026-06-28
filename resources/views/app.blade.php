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
        <style>
            #app-boot-splash {
                /* Keep in sync with splashContentScale in scripts/generate-nativephp-brand-assets.mjs */
                --splash-art-scale: 0.27;
                --splash-art-size: calc(min(100vw, 100vh) * var(--splash-art-scale));
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #ffffff;
                transition: opacity 0.4s ease, visibility 0.4s ease;
            }

            html.dark #app-boot-splash {
                background: #000000;
            }

            #app-boot-splash.app-boot-splash--hidden {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }

            #app-boot-splash .app-boot-splash__center {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: calc(var(--splash-art-size) * 0.14);
            }

            #app-boot-splash .app-boot-splash__art {
                display: block;
                width: var(--splash-art-size);
                height: var(--splash-art-size);
                object-fit: contain;
            }

            #app-boot-splash .app-boot-splash__art--dark {
                display: none;
            }

            @media (prefers-color-scheme: dark) {
                #app-boot-splash .app-boot-splash__art--light {
                    display: none;
                }

                #app-boot-splash .app-boot-splash__art--dark {
                    display: block;
                }
            }

            html.dark #app-boot-splash .app-boot-splash__art--light {
                display: none;
            }

            html.dark #app-boot-splash .app-boot-splash__art--dark {
                display: block;
            }

            #app-boot-splash .app-boot-splash__loader {
                position: relative;
                width: calc(var(--splash-art-size) * 0.17);
                height: calc(var(--splash-art-size) * 0.17);
            }

            #app-boot-splash .app-boot-splash__ring,
            #app-boot-splash .app-boot-splash__ring::after {
                position: absolute;
                inset: 0;
                border-radius: 9999px;
            }

            #app-boot-splash .app-boot-splash__ring {
                border: 2px solid rgb(0 86 63 / 0.16);
                border-top-color: #00563F;
                animation: app-boot-spin 0.9s cubic-bezier(0.55, 0.15, 0.35, 0.85) infinite;
            }

            html.dark #app-boot-splash .app-boot-splash__ring {
                border-color: rgb(170 221 207 / 0.18);
                border-top-color: #AADDCF;
            }

            #app-boot-splash .app-boot-splash__ring::after {
                content: '';
                inset: 22%;
                border: 1px solid transparent;
                border-top-color: rgb(0 86 63 / 0.55);
                animation: app-boot-spin 1.15s cubic-bezier(0.55, 0.15, 0.35, 0.85) infinite reverse;
            }

            html.dark #app-boot-splash .app-boot-splash__ring::after {
                border-top-color: rgb(170 221 207 / 0.65);
            }

            @keyframes app-boot-spin {
                to {
                    transform: rotate(360deg);
                }
            }
        </style>
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
        <div id="app-boot-splash" class="app-boot-splash" aria-hidden="true">
            <div class="app-boot-splash__center">
                <img
                    src="/brand/agora_splash_light.png"
                    alt=""
                    class="app-boot-splash__art app-boot-splash__art--light"
                    decoding="async"
                >
                <img
                    src="/brand/agora_splash_dark.png"
                    alt=""
                    class="app-boot-splash__art app-boot-splash__art--dark"
                    decoding="async"
                >
                <div class="app-boot-splash__loader" aria-label="Loading">
                    <span class="app-boot-splash__ring"></span>
                </div>
            </div>
        </div>
        <div id="app"></div>
    </body>
</html>
