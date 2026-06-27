# Configure winget PHP for Laravel / NativePHP on Windows
$ErrorActionPreference = "Stop"

function Test-PhpExtension {
    param([string]$Name)

    $result = & php -r "echo extension_loaded('$Name') ? '1' : '0';" 2>$null
    return "$result" -eq '1'
}

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Host "ERROR: php not on PATH." -ForegroundColor Red
    exit 1
}

$phpDir = Split-Path $php.Source
$iniPath = Join-Path $phpDir "php.ini"
$template = Join-Path $phpDir "php.ini-development"

if (-not (Test-Path $template)) {
    $template = Join-Path $phpDir "php.ini-production"
}

if (-not (Test-Path $iniPath)) {
    if (-not (Test-Path $template)) {
        Write-Host "ERROR: No php.ini template found in $phpDir" -ForegroundColor Red
        exit 1
    }
    Copy-Item $template $iniPath
    Write-Host "Created $iniPath" -ForegroundColor Green
}

$content = Get-Content $iniPath -Raw

if ($content -notmatch '(?m)^extension_dir\s*=') {
    $content = $content -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
}

$extensions = @(
    'curl',
    'fileinfo',
    'mbstring',
    'openssl',
    'pdo_sqlite',
    'sqlite3',
    'zip',
    'sodium',
    'gd',
    'intl',
    'pdo_pgsql',
    'pgsql'
)

foreach ($ext in $extensions) {
    $content = $content -replace ";extension=$ext(\s*;.*)?", "extension=$ext"
    $content = $content -replace ";extension=php_$ext\.dll(\s*;.*)?", "extension=php_$ext.dll"
}

Set-Content -Path $iniPath -Value $content -NoNewline

Write-Host "Enabled PHP extensions in $iniPath" -ForegroundColor Green

$required = @('mbstring', 'curl', 'openssl', 'fileinfo', 'zip')
$missing = @()

foreach ($name in $required) {
    if (-not (Test-PhpExtension $name)) {
        $missing += $name
    }
}

if ($missing.Count -gt 0) {
    Write-Host "ERROR: PHP is still missing extensions: $($missing -join ', ')" -ForegroundColor Red
    Write-Host "Run: php --ini" -ForegroundColor Yellow
    Write-Host "Loaded modules:" -ForegroundColor Yellow
    & php -m
    exit 1
}

Write-Host "PHP extensions OK (mbstring, curl, openssl, fileinfo, zip)." -ForegroundColor Green
