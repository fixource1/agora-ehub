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
subst W: \\wsl.localhost\Ubuntu\home\dom\agora-ehub
W:
scripts\native-install-windows.bat
```

CMD cannot use a UNC path as the working directory — map a drive letter with `subst` first.

### PowerShell (recommended on Windows)

`set VAR=value` is CMD syntax and does not work in PowerShell. Use:

```powershell
subst W: \\wsl.localhost\Ubuntu\home\dom\agora-ehub
W:
```

If scripts are blocked (`running scripts is disabled`), either bypass for this session:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\scripts\native-setup-windows.ps1
```

Or use the `.cmd` wrappers (no policy change needed):

```powershell
.\scripts\native-setup-windows.cmd
.\scripts\native-install-windows.cmd
php artisan native:emulator android
.\scripts\native-run-android.cmd
```

### CMD

```cmd
subst W: \\wsl.localhost\Ubuntu\home\dom\agora-ehub
W:
set ANDROID_HOME=%LOCALAPPDATA%\Android\Sdk
set PATH=%ANDROID_HOME%\platform-tools;%ANDROID_HOME%\emulator;%PATH%
scripts\native-install-windows.bat
```

### Install PHP on Windows (required)

PHP is not available in WSL for NativePHP Android. Install on Windows:

```powershell
winget install PHP.PHP.8.4
winget install Microsoft.VCRedist.2015+.x64
```

**Important:** After installing PHP via winget, you must also install the **Visual C++ Redistributable** or PHP will crash with:

`VCRUNTIME140.dll 14.29 is not compatible with this PHP build linked with 14.44`

Close and reopen PowerShell after installing both, then verify PHP actually runs:

```powershell
php -r "echo function_exists('mb_strimwidth') ? 'ok' : 'missing mbstring';"
php -m | findstr mbstring
php -v
adb version
```

If `mbstring` is missing, winget PHP needs a `php.ini`. Run once:

```powershell
.\scripts\configure-windows-php.cmd
```

If problems persist, use [Laravel Herd for Windows](https://herd.laravel.com/windows) instead of winget PHP.

### Java (required for Gradle)

Android Studio bundles a JDK. The setup scripts auto-detect:

`C:\Program Files\Android\Android Studio\jbr`

Or set manually in PowerShell before building:

```powershell
$env:JAVA_HOME = "C:\Program Files\Android\Android Studio\jbr"
$env:PATH = "$env:JAVA_HOME\bin;$env:PATH"
java -version
```

### Composer on Windows (required for app bundle)

Composer is not available via winget. From the project folder:

```powershell
.\scripts\install-windows-composer.cmd
composer -V
```

Then close and reopen PowerShell, or run `.\scripts\native-setup-windows.cmd` (it picks up the local install).

### PHP static libraries (required for Gradle)

If the build log shows `libphp.a not found`, PHP binaries were not extracted during install.

**If `native:install` shows `Failed to fetch versions manifest`** — Windows PHP often cannot reach `bin.nativephp.com`. Use the manual installer:

From WSL:

```bash
cd ~/agora-ehub
./scripts/install-android-php-binaries.sh 8.4 /mnt/c/Users/domin/agora-ehub
```

From Windows PowerShell:

```powershell
.\scripts\install-android-php-binaries.cmd
```

Verify:

```powershell
dir nativephp\android\app\src\main\staticLibs\arm64-v8a\libphp.a
```


Building from `W:` (subst to WSL) can cause `robocopy failed with exit code 16` and a **0 MB bundle**.
If that happens, copy the project to a native Windows path and build there:

```powershell
xcopy \\wsl.localhost\Ubuntu\home\dom\agora-ehub C:\Users\domin\agora-ehub /E /I /H /Y
cd C:\Users\domin\agora-ehub
.\scripts\native-run-android.cmd
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

## Android emulator (Windows)

All steps below use **Windows CMD**, not WSL. Map the project first:

```cmd
subst W: \\wsl.localhost\Ubuntu\home\dom\agora-ehub
W:
```

### 1. Install Android Studio

1. Install [Android Studio](https://developer.android.com/studio).
2. Open **SDK Manager** and install:
   - Android SDK Platform (API 34+ recommended)
   - Android SDK Build-Tools
   - Android SDK Platform-Tools
   - Android Emulator
3. Open **Device Manager** → **Create Virtual Device** → pick a phone (e.g. Pixel 7) → download a system image → finish.

### 2. Set Android SDK on PATH (Windows)

In CMD (adjust username if needed):

```cmd
set ANDROID_HOME=%LOCALAPPDATA%\Android\Sdk
set PATH=%ANDROID_HOME%\platform-tools;%ANDROID_HOME%\emulator;%PATH%
```

Optional in `.env`:

```env
NATIVEPHP_ANDROID_SDK_LOCATION=C:\Users\YOUR_USER\AppData\Local\Android\Sdk
```

### 3. One-time NativePHP setup

Keep Docker running in WSL (`sudo docker compose up -d`). Build frontend assets once:

```bash
sudo docker compose --profile dev run --rm node sh -c "npm install && npm run build"
```

Then in **Windows CMD**:

```cmd
W:
scripts\native-install-windows.bat
```

### 4. Start the emulator

Either launch from **Android Studio → Device Manager → Play**, or in **Windows CMD**:

```cmd
W:
php artisan native:emulator android
```

Wait until the emulator is fully booted (`adb devices` should show `device`, not `offline`).

### 5. Run the app

```cmd
W:
scripts\native-run-android.bat
```

Or with hot reload during development:

```cmd
php artisan native:run android --watch
```

The app opens at `NATIVEPHP_START_URL` (`/home`).

### Troubleshooting

| Issue | Fix |
|-------|-----|
| `Android is not supported in WSL` | Run from Windows CMD, not WSL |
| `No Android project found` | Run `scripts\native-install-windows.bat` first |
| `ADB is not installed` | Add `%ANDROID_HOME%\platform-tools` to PATH |
| `No emulators (AVDs) found` | Create an AVD in Android Studio Device Manager |
| `PHP is not on PATH` | Install [Laravel Herd](https://herd.laravel.com/windows) |
| Build log | `nativephp/android-build.log` |
| Profile shows Embedded API but you set remote API | Edit `config/nativephp.env`, bump `NATIVEPHP_APP_VERSION_CODE`, rebuild APK |
| `.env` mobile settings disappear after WSL sync | Use `config/nativephp.env` instead of editing `.env` directly |
| Remote API test fails (`10.0.2.2`) with Docker running | WSL2 often needs the **WSL IP** instead. Run `scripts\setup-android-api-access.cmd` on Windows, then rebuild. Ensure `curl http://127.0.0.1:8080/api/v1/resources` works in PowerShell. |

## Environment variables

### WSL vs Windows `.env`

`./scripts/sync-to-windows.sh` **does not copy `.env`** anymore. WSL and Windows keep separate `.env` files (Docker vs NativePHP).

For **Android API mode and version code**, edit **`config/nativephp.env`** on Windows (this file is synced from WSL). Before each build, `prepare-native-build-windows.ps1` merges it into `.env` automatically.

```env
# config/nativephp.env — survives WSL sync
MOBILE_API_ENABLED=true
MOBILE_API_BASE_URL=http://10.0.2.2:8080
NATIVEPHP_APP_VERSION_CODE=7
```

| Mode | Profile shows | When to use |
|------|---------------|-------------|
| `MOBILE_API_ENABLED=false` | Embedded API (`127.0.0.1`) | Offline SQLite on device |
| `MOBILE_API_ENABLED=true` | Remote API (`10.0.2.2:8080`) | Live Docker on WSL from emulator |

After changing `config/nativephp.env`, bump `NATIVEPHP_APP_VERSION_CODE`, run `npm run build`, then `scripts\native-run-android.cmd`. Clear app storage on the emulator if the Profile screen still shows the old mode.

Base NativePHP keys (in `.env` or `.env.example`):

```env
NATIVEPHP_APP_ID=com.uplb.agora.ehub
NATIVEPHP_APP_VERSION=1.0.0
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
