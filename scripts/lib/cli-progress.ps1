# Simple terminal progress helpers for Windows PowerShell scripts.
# Usage: . "$PSScriptRoot\lib\cli-progress.ps1"

function Write-CliTitle {
    param([string]$Message)
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Write-CliNote {
    param([string]$Message)
    Write-Host $Message -ForegroundColor DarkGray
}

function Write-CliDone {
    param([string]$Message)
    Write-Host "[OK] $Message" -ForegroundColor Green
}

function Write-CliFail {
    param([string]$Message)
    Write-Host "[FAIL] $Message" -ForegroundColor Red
}

function Write-CliProgressBar {
    param(
        [int]$Percent = 0,
        [string]$Label = ''
    )

    if ($Percent -lt 0) { $Percent = 0 }
    if ($Percent -gt 100) { $Percent = 100 }

    $width = 40
    $filled = [math]::Floor($Percent * $width / 100)
    $empty = $width - $filled
    $bar = ('#' * $filled) + ('-' * $empty)

    Write-Host ("  [{0}] {1,3}% {2}" -f $bar, $Percent, $Label) -NoNewline
    Write-Host ''
}

function Invoke-CliStep {
    param(
        [string]$Title,
        [scriptblock]$Action
    )

    Write-CliTitle $Title
    & $Action
    if ($LASTEXITCODE -and $LASTEXITCODE -ne 0) {
        throw ('Step failed: {0} (exit {1})' -f $Title, $LASTEXITCODE)
    }
}

function Resolve-NpmCmd {
    $candidate = Get-Command npm.cmd -ErrorAction SilentlyContinue
    if ($candidate) {
        return $candidate.Source
    }

    $fallback = Join-Path ${env:ProgramFiles} 'nodejs\npm.cmd'
    if (Test-Path $fallback) {
        return $fallback
    }

    return $null
}

function Add-NodeToPath {
    $npmCmd = Resolve-NpmCmd
    if (-not $npmCmd) {
        return $false
    }

    $nodeDir = Split-Path -Parent $npmCmd
    if ($nodeDir -and $env:PATH -notlike "*$nodeDir*") {
        $env:PATH = "$nodeDir;$env:PATH"
    }

    return $true
}
