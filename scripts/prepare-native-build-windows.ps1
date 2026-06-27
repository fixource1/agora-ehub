# Fix Windows-specific issues before NativePHP bundles/builds for Android.
$ErrorActionPreference = 'Stop'
$ConfirmPreference = 'None'

. (Join-Path $PSScriptRoot 'lib\cli-progress.ps1')

$projectRoot = if ($PSScriptRoot) {
    Split-Path -Parent $PSScriptRoot
} else {
    (Get-Location).Path
}

& (Join-Path $PSScriptRoot 'apply-nativephp-env.ps1') -ProjectRoot $projectRoot
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

function Write-GradleSdkDir {
    param([string]$SdkPath)

    if ($SdkPath -match '^([A-Za-z]):\\(.*)$') {
        $escaped = $Matches[2] -replace '\\', '\\\\'
        return ('{0}\:{1}' -f $Matches[1], $escaped)
    }

    if ($SdkPath -match '^([A-Za-z]):/(.*)$') {
        return ('{0}:/{1}' -f $Matches[1], $Matches[2])
    }

    throw "Invalid SDK path: $SdkPath"
}

function Test-AndroidSdk {
    param([string]$Candidate)

    if (-not $Candidate -or $Candidate -notmatch '^[A-Za-z]:[\\/].+') {
        return $false
    }

    return Test-Path -LiteralPath (Join-Path $Candidate 'platform-tools')
}

function Get-AndroidSdk {
    $candidates = @(
        (Join-Path $env:LOCALAPPDATA 'Android\Sdk'),
        $env:ANDROID_HOME,
        $env:ANDROID_SDK_ROOT
    )

    foreach ($candidate in $candidates) {
        if (Test-AndroidSdk $candidate) {
            return $candidate
        }
    }

    return $null
}

function Set-EnvSdkLocation {
    param(
        [string]$EnvFile,
        [string]$SdkPath
    )

    if (-not (Test-Path -LiteralPath $EnvFile)) {
        return
    }

    $line = "NATIVEPHP_ANDROID_SDK_LOCATION=$SdkPath"
    $content = Get-Content -LiteralPath $EnvFile -Encoding utf8

    if ($content -match '^\s*NATIVEPHP_ANDROID_SDK_LOCATION=') {
        $content = $content | ForEach-Object {
            if ($_ -match '^\s*NATIVEPHP_ANDROID_SDK_LOCATION=') { $line } else { $_ }
        }
    } else {
        $content += ''
        $content += '# Android SDK for NativePHP Gradle builds (Windows)'
        $content += $line
    }

    Set-Content -LiteralPath $EnvFile -Value $content -Encoding utf8
}

function Test-JavaInstall {
    param([string]$Candidate)

    if (-not $Candidate -or $Candidate -notmatch '^[A-Za-z]:\\') {
        return $false
    }

    return Test-Path -LiteralPath (Join-Path $Candidate 'bin\java.exe')
}

function Get-JavaInstall {
    $candidates = @(
        (Join-Path $env:ProgramFiles 'Android\Android Studio\jbr'),
        (Join-Path $env:LOCALAPPDATA 'Programs\Android\Android Studio\jbr')
    )

    if (Test-JavaInstall $env:JAVA_HOME) {
        $candidates += $env:JAVA_HOME
    }

    foreach ($candidate in $candidates) {
        if (Test-JavaInstall $candidate) {
            return $candidate
        }
    }

    return $null
}

function Remove-ReparseItem {
    param([string]$Path)

    if (-not (Test-Path -LiteralPath $Path)) {
        return
    }

    cmd /c "rmdir `"$Path`"" 2>$null | Out-Null

    if (Test-Path -LiteralPath $Path) {
        Remove-Item -LiteralPath $Path -Force -Recurse -Confirm:$false
    }
}

# --- public/storage: replace Docker symlinks with a Windows junction ---
$publicStorage = Join-Path $projectRoot 'public\storage'
$storagePublic = Join-Path $projectRoot 'storage\app\public'

New-Item -ItemType Directory -Force -Path $storagePublic | Out-Null

if (Test-Path -LiteralPath $publicStorage) {
    $item = Get-Item -LiteralPath $publicStorage -Force
    $isReparsePoint = ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0

    if ($isReparsePoint) {
        Remove-ReparseItem $publicStorage
        Write-Host 'Removed public\storage symlink/junction' -ForegroundColor Yellow
    }
} else {
    $publicDir = Join-Path $projectRoot 'public'
    $maybeLink = Get-ChildItem -LiteralPath $publicDir -Force -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -eq 'storage' }

    if ($maybeLink) {
        Remove-ReparseItem $publicStorage
        Write-Host 'Removed broken public\storage link' -ForegroundColor Yellow
    }
}

if (-not (Test-Path -LiteralPath $publicStorage)) {
    $target = (Resolve-Path $storagePublic).Path
    New-Item -ItemType Junction -Path $publicStorage -Target $target | Out-Null
    Write-Host 'Linked public\storage -> storage\app\public' -ForegroundColor Green
}

# --- Android SDK path for Gradle (local.properties) ---
$sdkPath = Get-AndroidSdk

if (-not $sdkPath) {
    Write-Host '[FAIL] Android SDK not found. Install Android Studio first.' -ForegroundColor Red
    exit 1
}

$env:ANDROID_HOME = $sdkPath
$env:ANDROID_SDK_ROOT = $sdkPath
$env:NATIVEPHP_ANDROID_SDK_LOCATION = $sdkPath

$localProperties = Join-Path $projectRoot 'nativephp\android\local.properties'
$sdkDir = Write-GradleSdkDir $sdkPath
Set-Content -Path $localProperties -Value "sdk.dir=$sdkDir" -Encoding ascii -NoNewline
Add-Content -Path $localProperties -Value '' -Encoding ascii
Set-EnvSdkLocation (Join-Path $projectRoot '.env') $sdkPath
Write-Host "Wrote nativephp\android\local.properties for $sdkPath" -ForegroundColor Green

# --- Java for Gradle ---
$javaHome = Get-JavaInstall

if ($javaHome) {
    $env:JAVA_HOME = $javaHome
    $env:PATH = "$($env:JAVA_HOME)\bin;$env:PATH"
    Write-Host "JAVA_HOME = $($env:JAVA_HOME)" -ForegroundColor Green
} else {
    Write-Host 'WARNING: Java not found. Set JAVA_HOME to Android Studio jbr.' -ForegroundColor Yellow
}

# --- NativePHP launcher icon + splash (public/icon.png, public/splash*.png) ---
if (-not (Add-NodeToPath)) {
    Write-Host '[FAIL] Node.js not found. Install Node before building the Android app.' -ForegroundColor Red
    exit 1
}

$npmCmd = Resolve-NpmCmd

if (-not (Test-Path (Join-Path $projectRoot 'node_modules\sharp'))) {
    Write-Host 'Installing sharp for NativePHP brand asset generation...' -ForegroundColor Cyan
    Push-Location $projectRoot
    & $npmCmd install
    if ($LASTEXITCODE -ne 0) {
        Pop-Location
        Write-Host '[FAIL] npm install failed.' -ForegroundColor Red
        exit 1
    }
    Pop-Location
}

Write-Host 'Generating NativePHP icon and splash PNGs...' -ForegroundColor Cyan
Push-Location $projectRoot
& $npmCmd run build:native-brand
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    Write-Host '[FAIL] Native brand asset generation failed.' -ForegroundColor Red
    exit 1
}
Pop-Location
Write-Host 'NativePHP icon and splash PNGs ready.' -ForegroundColor Green

# NativePHP re-processes public/icon.png with PHP GD. Our Node build already wrote android res.
foreach ($legacyBrandFile in @('public\icon.png', 'public\splash.png', 'public\splash-dark.png')) {
    $legacyPath = Join-Path $projectRoot $legacyBrandFile

    if (Test-Path -LiteralPath $legacyPath) {
        Remove-Item -LiteralPath $legacyPath -Force
        Write-Host "Removed $legacyBrandFile so native:run does not require PHP GD." -ForegroundColor DarkGray
    }
}
