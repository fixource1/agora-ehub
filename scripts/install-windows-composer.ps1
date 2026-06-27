# Install Composer for Windows (PHP Composer is not on winget)
$ErrorActionPreference = "Stop"

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Host "ERROR: php not on PATH. Install PHP first." -ForegroundColor Red
    exit 1
}

$installDir = Join-Path $env:LOCALAPPDATA "Composer"
New-Item -ItemType Directory -Force -Path $installDir | Out-Null

$installer = Join-Path $env:TEMP "composer-setup.php"
$expected = (Invoke-RestMethod -Uri "https://composer.github.io/installer.sig" -UseBasicParsing).Trim()

Invoke-WebRequest -Uri "https://getcomposer.org/installer" -OutFile $installer -UseBasicParsing
$actual = (Get-FileHash $installer -Algorithm SHA384).Hash.ToLower()

if ($actual -ne $expected) {
    Write-Host "ERROR: Composer installer checksum mismatch." -ForegroundColor Red
    exit 1
}

& php $installer --install-dir=$installDir --filename=composer.phar
Remove-Item $installer -Force

$bat = Join-Path $installDir "composer.bat"
@(
    '@echo off',
    'php "%~dp0composer.phar" %*'
) | Set-Content -Path $bat -Encoding ASCII

$env:PATH = "$installDir;$env:PATH"
[Environment]::SetEnvironmentVariable("Path", "$installDir;" + [Environment]::GetEnvironmentVariable("Path", "User"), "User")

Write-Host "Composer installed to $installDir" -ForegroundColor Green
& php (Join-Path $installDir "composer.phar") -V
