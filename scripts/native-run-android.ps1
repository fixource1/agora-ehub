# Build and run on Android emulator (Windows PowerShell only, not WSL)
$ErrorActionPreference = "Stop"

. "$PSScriptRoot\native-setup-windows.ps1"
if (-not $script:NativePhpSetupOk) { exit 1 }

& "$PSScriptRoot\prepare-native-build-windows.ps1"
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host ""
Write-Host "Running on Android emulator..." -ForegroundColor Cyan
php artisan native:run android @args
