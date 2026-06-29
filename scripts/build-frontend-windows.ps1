# Build Vite frontend assets on Windows (npm install + npm run build).
$ErrorActionPreference = 'Stop'

. "$PSScriptRoot\lib\cli-progress.ps1"

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

$node = Get-Command node.exe -ErrorAction SilentlyContinue
if (-not $node) {
    $node = Get-Command node -ErrorAction SilentlyContinue
}

$npmCmd = Resolve-NpmCmd

if (-not $node -or -not $npmCmd) {
    Write-CliFail 'Node.js or npm.cmd was not found on PATH.'
    Write-CliNote 'Install Node.js first:'
    Write-CliNote '  .\scripts\install-windows-node.cmd'
    Write-CliNote 'Then close and reopen PowerShell.'
    exit 1
}

Add-NodeToPath | Out-Null

Write-CliTitle 'Frontend build'
Write-CliNote "Node $(& $node.Source -v) | npm $(& $npmCmd -v)"
Write-Host ''

Write-CliTitle 'Installing npm packages'
Write-CliNote 'Syncing dependencies from package-lock.json...'
Write-Host ''
& $npmCmd install
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
Write-Host ''
Write-CliDone 'npm install complete'
Write-Host ''

Write-CliTitle 'Building assets (Vite)'
Write-CliNote 'Compiling Vue, Tailwind, and bundled images...'
Write-Host ''
& $npmCmd run build
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

if (-not (Test-Path 'public\pdf\pdfium.wasm')) {
    Write-CliFail 'public\pdf\pdfium.wasm was not created by the frontend build.'
    Write-CliNote 'Re-run: npm run build'
    exit 1
}

if (-not (Test-Path 'public\pdf\pdf.worker.min.mjs')) {
    Write-CliFail 'public\pdf\pdf.worker.min.mjs was not created by the frontend build.'
    Write-CliNote 'Re-run: npm run build'
    exit 1
}

Write-Host ''
Write-CliTitle 'Generating NativePHP icon and splash PNGs'
Write-CliNote 'From public\brand\agora-icon.svg and AGORA Seal SVG White.svg'
Write-Host ''
& $npmCmd run build:native-brand
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host ''
Write-CliDone 'Frontend build complete'
Write-CliNote 'Output: public\build\, nativephp\android\app\src\main\res\'
Write-CliNote 'Next: .\scripts\native-run-android.cmd'
