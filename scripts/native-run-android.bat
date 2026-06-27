@echo off
REM Build and run AGORA e-Hub on Android emulator (Windows CMD only, not WSL)
cd /d "%~dp0\.."
echo Project: %CD%
where php >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not on PATH. Install Laravel Herd or PHP for Windows first.
    pause
    exit /b 1
)
where adb >nul 2>&1
if errorlevel 1 (
    echo WARNING: adb not on PATH. Add Android SDK platform-tools to PATH, or set ANDROID_HOME.
    echo Example: set ANDROID_HOME=%LOCALAPPDATA%\Android\Sdk
    echo          set PATH=%%ANDROID_HOME%%\platform-tools;%%ANDROID_HOME%%\emulator;%%PATH%%
)
php artisan native:run android %*
