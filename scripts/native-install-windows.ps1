# Install NativePHP Android project (Windows PowerShell only, not WSL)
$ErrorActionPreference = "Stop"

. "$PSScriptRoot\native-setup-windows.ps1"
if (-not $script:NativePhpSetupOk) { exit 1 }

Write-Host ""
Write-Host "Installing NativePHP for AGORA e-Hub..." -ForegroundColor Cyan
php artisan native:install android --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: native:install failed (exit $LASTEXITCODE)." -ForegroundColor Red
    exit $LASTEXITCODE
}

if (-not (Test-Path "nativephp\android")) {
    Write-Host "ERROR: native:install finished but nativephp\android was not created." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "NativePHP Android project installed." -ForegroundColor Green
Write-Host "Next: php artisan native:emulator android" -ForegroundColor Green
Write-Host "Then: .\scripts\native-run-android.cmd" -ForegroundColor Green
