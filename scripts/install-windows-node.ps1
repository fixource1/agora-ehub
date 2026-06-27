# Install Node.js LTS on Windows (required for npm run build / Vite).
$ErrorActionPreference = 'Stop'

. "$PSScriptRoot\lib\cli-progress.ps1"

$node = Get-Command node.exe -ErrorAction SilentlyContinue
if (-not $node) {
    $node = Get-Command node -ErrorAction SilentlyContinue
}

$npmCmd = Resolve-NpmCmd

if ($node -and $npmCmd) {
    Write-CliDone 'Node.js already installed'
    & $node.Source -v
    & $npmCmd -v
    exit 0
}

Write-CliTitle 'Installing Node.js LTS via winget'
Write-CliNote 'This adds node and npm to your user PATH.'

$winget = Get-Command winget -ErrorAction SilentlyContinue
if (-not $winget) {
    Write-CliFail 'winget not found. Install Node manually: https://nodejs.org/'
    exit 1
}

& winget install OpenJS.NodeJS.LTS --accept-package-agreements --accept-source-agreements

Write-Host ''
Write-CliDone 'Node.js installed'
Write-CliNote 'Close and reopen PowerShell, then run:'
Write-CliNote '  .\scripts\build-frontend-windows.cmd'
Write-CliNote 'If you run npm manually in PowerShell, use npm.cmd (not npm).'
