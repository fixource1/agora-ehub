# NativePHP for AGORA e-Hub

NativePHP Mobile is **installed** (`nativephp/mobile ^3.3`). The Laravel package, config, and offline download API are ready. Native Android project files must be generated from **Windows CMD** (not WSL).

## What's configured

| Item | Status |
|------|--------|
| `nativephp/mobile` Composer package | ✅ Installed |
| `config/nativephp.php` | ✅ Published |
| `NATIVEPHP_APP_ID` and start URL (`/home`) | ✅ In `.env` |
| `./native` CLI wrapper | ✅ Copied from package |
| Offline download API | ✅ `POST /api/v1/downloads` |
| Vue `useNative()` composable | ✅ Detects NativePHP shell |
| Responsive UI (phone + tablet) | ✅ |

## WSL limitation

`php artisan native:install` **cannot complete Android setup inside WSL**. Run from Windows:

```cmd
cd \\wsl.localhost\Ubuntu\home\dom\AGORA_e-HUB
scripts\native-install-windows.bat
```

Or in PowerShell/CMD with PHP on PATH:

```cmd
php artisan native:install android
php artisan native:run android
```

## Development workflow

### Web / tablet preview (now)

```bash
docker compose up -d
npm run build   # or npm run dev
```

Open http://localhost:8080 and resize to **768px+** for tablet layout (persistent sidebar, 3-column resource detail, 3-pane reader).

### NativePHP mobile test server

After `native:install` on Windows:

```cmd
php artisan native:jump
```

Loads the Laravel app in the NativePHP shell with hot reload.

### Build Android APK

```cmd
php artisan native:run android
php artisan native:package android
```

## Environment variables

```env
NATIVEPHP_APP_ID=com.uplb.agora.ehub
NATIVEPHP_APP_VERSION=1.0.0
NATIVEPHP_APP_VERSION_CODE=1
NATIVEPHP_START_URL=/home
NATIVEPHP_DEEPLINK_SCHEME=agora-ehub
NATIVEPHP_DEEPLINK_HOST=agora-ehub.local
```

## UI breakpoints

| Breakpoint | Layout |
|------------|--------|
| `< 768px` | Phone: bottom nav, drawer sidebar, stacked pages |
| `≥ 768px` | Tablet: persistent library sidebar, wider grids |
| `≥ 1024px` | Large tablet: 3-column resource detail, 3-pane reader |

## Offline sync API

```
GET  /api/v1/downloads        List device downloads
POST /api/v1/downloads        Mark resource downloaded
     { resource_id, device_id?, device_name? }
```

Headers for NativePHP builds:

- `X-Device-Id` — unique device identifier
- `X-Device-Name` — human-readable device name

## Windows desktop (optional next step)

For a Windows desktop app, add `nativephp/electron` separately:

```bash
composer require nativephp/electron
php artisan native:install
```

This packages the same Vue UI in an Electron shell for offline desktop use.
