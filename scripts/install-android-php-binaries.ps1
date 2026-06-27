# Download NativePHP Android PHP binaries (Windows fallback when artisan cannot fetch manifest)
param(
    [string]$PhpMinor = "8.4",
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = "Stop"

$zipName = switch ($PhpMinor) {
    "8.3" { "android-3.1.0-php8.3.31.zip" }
    "8.4" { "android-3.1.0-php8.4.22.zip" }
    "8.5" { "android-3.1.0-php8.5.7.zip" }
    default { throw "Unsupported PHP version: $PhpMinor" }
}

$url = "https://bin.nativephp.com/main/$PhpMinor/android/$zipName"
$dest = Join-Path $ProjectRoot "nativephp\android\app\src\main"
$tmp = Join-Path $env:TEMP "nativephp-php-$([guid]::NewGuid().ToString())"
$zip = Join-Path $tmp "php-android.zip"
$sevenZip = "C:\Program Files\7-Zip\7z.exe"

New-Item -ItemType Directory -Force -Path $tmp | Out-Null

Write-Host "Downloading $url" -ForegroundColor Cyan
Invoke-WebRequest -Uri $url -OutFile $zip -UseBasicParsing

if (Test-Path $sevenZip) {
    & $sevenZip x $zip "-o$tmp\out" -y | Out-Null
} else {
    Expand-Archive -Path $zip -DestinationPath "$tmp\out" -Force
}

if (-not (Test-Path "$dest")) {
    New-Item -ItemType Directory -Force -Path $dest | Out-Null
}

Remove-Item -Recurse -Force "$dest\staticLibs", "$dest\include" -ErrorAction SilentlyContinue
Copy-Item -Recurse "$tmp\out\staticLibs", "$tmp\out\include" $dest
Remove-Item -Recurse -Force $tmp

$libphp = Join-Path $dest "staticLibs\arm64-v8a\libphp.a"
if (-not (Test-Path $libphp)) {
    Write-Host "ERROR: libphp.a not found after extract" -ForegroundColor Red
    exit 1
}

$phpVersion = if ($PhpMinor -eq "8.3") { "8.3.31" } elseif ($PhpMinor -eq "8.4") { "8.4.22" } else { "8.5.7" }
@{
    php = @{
        version = $phpVersion
        icu = $false
    }
} | ConvertTo-Json -Depth 3 | Set-Content (Join-Path $ProjectRoot "nativephp.lock")

Write-Host "Installed PHP $PhpMinor binaries." -ForegroundColor Green
Write-Host "Next: .\scripts\native-run-android.cmd" -ForegroundColor Green
