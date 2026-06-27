@echo off
REM Run NativePHP Android install from Windows (not WSL)
cd /d "%~dp0\.."
echo Installing NativePHP for AGORA e-Hub...
echo Project: %CD%
where php >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not on PATH. Install Laravel Herd or PHP for Windows first.
    pause
    exit /b 1
)
php artisan native:install android --no-interaction
echo.
echo Next: php artisan native:run android
pause
