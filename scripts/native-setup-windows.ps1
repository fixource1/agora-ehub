# Shared Android SDK + PHP checks for Windows PowerShell
$script:NativePhpSetupOk = $true

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

$sdk = Join-Path $env:LOCALAPPDATA 'Android\Sdk'
if ((Test-Path -LiteralPath (Join-Path $sdk 'platform-tools'))) {
    $env:ANDROID_HOME = $sdk
    $env:ANDROID_SDK_ROOT = $sdk
    $env:PATH = "$sdk\platform-tools;$sdk\emulator;$env:PATH"
    Write-Host "ANDROID_HOME = $env:ANDROID_HOME" -ForegroundColor Green
} else {
    Write-Host "WARNING: Android SDK not found at $sdk" -ForegroundColor Yellow
    Write-Host "Install Android Studio and the Android SDK first." -ForegroundColor Yellow
}

$javaHome = $null
foreach ($candidate in @(
    (Join-Path $env:ProgramFiles 'Android\Android Studio\jbr'),
    (Join-Path $env:LOCALAPPDATA 'Programs\Android\Android Studio\jbr')
)) {
    if ($candidate -match '^[A-Za-z]:\\' -and (Test-Path -LiteralPath (Join-Path $candidate 'bin\java.exe'))) {
        $javaHome = $candidate
        break
    }
}

if (-not $javaHome -and $env:JAVA_HOME -match '^[A-Za-z]:\\' -and (Test-Path -LiteralPath (Join-Path $env:JAVA_HOME 'bin\java.exe'))) {
    $javaHome = $env:JAVA_HOME
}

if ($javaHome) {
    $env:JAVA_HOME = $javaHome
    $env:PATH = "$($env:JAVA_HOME)\bin;$env:PATH"
    Write-Host "JAVA_HOME = $env:JAVA_HOME" -ForegroundColor Green
} else {
    Write-Host "WARNING: Java not found. Install Android Studio or set JAVA_HOME." -ForegroundColor Yellow
}

$composer = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composer) {
    $localComposer = Join-Path $env:LOCALAPPDATA "Composer\composer.bat"
    if (Test-Path $localComposer) {
        $env:PATH = "$(Split-Path $localComposer);$env:PATH"
        $composer = Get-Command composer -ErrorAction SilentlyContinue
    }
}
if (-not $composer) {
    Write-Host "WARNING: composer is not on PATH. NativePHP bundle builds need it." -ForegroundColor Yellow
    Write-Host "Install with: .\scripts\install-windows-composer.cmd" -ForegroundColor Yellow
} else {
    Write-Host "Composer = $($composer.Source)" -ForegroundColor Green
}

if ((Get-Location).Drive.Name -eq 'W') {
    Write-Host "NOTE: Building from W: (WSL subst) can break robocopy. If bundle size is 0 MB," -ForegroundColor Yellow
    Write-Host "copy the project to a Windows folder (e.g. C:\Users\domin\agora-ehub) and build there." -ForegroundColor Yellow
}

$staticLibs = Join-Path $projectRoot "nativephp\android\app\src\main\staticLibs\arm64-v8a\libphp.a"
if (-not (Test-Path $staticLibs)) {
    Write-Host ""
    Write-Host "ERROR: PHP static libraries missing (libphp.a)." -ForegroundColor Red
    Write-Host "Download them with:" -ForegroundColor Yellow
    Write-Host "  .\scripts\install-android-php-binaries.cmd"
    Write-Host ""
    Write-Host "Or re-run NativePHP install:" -ForegroundColor Yellow
    Write-Host "  php artisan native:install android --force --no-interaction"
    Write-Host ""
    Write-Host "7-Zip helps but is optional: C:\Program Files\7-Zip\7z.exe"
    $script:NativePhpSetupOk = $false
    return
}

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Host ""
    Write-Host "ERROR: PHP is not on PATH." -ForegroundColor Red
    Write-Host "Install one of:" -ForegroundColor Yellow
    Write-Host "  winget install PHP.PHP.8.4"
    Write-Host "  https://herd.laravel.com/windows"
    Write-Host ""
    Write-Host "Then close and reopen PowerShell, map the drive again, and re-run."
    $script:NativePhpSetupOk = $false
    return
}

Write-Host "PHP = $($php.Source)" -ForegroundColor Green

& "$PSScriptRoot\configure-windows-php.ps1"
if ($LASTEXITCODE -ne 0) {
    $script:NativePhpSetupOk = $false
    return
}

$phpTest = & php -r "echo function_exists('mb_strimwidth') && extension_loaded('mbstring') ? 'ok' : 'no-mbstring';" 2>&1 | Select-Object -Last 1
if ($LASTEXITCODE -ne 0 -or $phpTest -notmatch 'ok') {
    Write-Host ""
    Write-Host "ERROR: PHP is installed but mbstring is not available." -ForegroundColor Red
    Write-Host "Run: .\scripts\configure-windows-php.cmd" -ForegroundColor Yellow
    if ($phpTest) {
        Write-Host ""
        Write-Host "PHP output: $phpTest" -ForegroundColor DarkYellow
    }
    $script:NativePhpSetupOk = $false
    return
}

$node = Get-Command node.exe -ErrorAction SilentlyContinue
if (-not $node) {
    $node = Get-Command node -ErrorAction SilentlyContinue
}

. "$PSScriptRoot\lib\cli-progress.ps1"
$npmCmd = Resolve-NpmCmd
if (-not $node -or -not $npmCmd) {
    Write-Host "WARNING: npm is not on PATH. Frontend builds need Node.js." -ForegroundColor Yellow
    Write-Host "Install with: .\scripts\install-windows-node.cmd" -ForegroundColor Yellow
    Write-Host "Then build with: .\scripts\build-frontend-windows.cmd" -ForegroundColor Yellow
} else {
    Write-Host "Node = $(& $node.Source -v) | npm = $(& $npmCmd -v)" -ForegroundColor Green
}

php -v
