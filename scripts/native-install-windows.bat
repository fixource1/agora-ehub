@echo off
REM Run NativePHP Android install from Windows (not WSL)
cd /d "%~dp0"
echo Installing NativePHP for AGORA e-Hub...
php artisan native:install android --no-interaction
echo.
echo Next: php artisan native:run android
pause
