# Merge config/nativephp.env into the project .env before NativePHP Android builds.
# Keeps Windows-only settings (mobile API URL, version code) out of WSL sync paths.
param(
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = 'Stop'

$envFile = Join-Path $ProjectRoot '.env'
$nativeEnvFile = Join-Path $ProjectRoot 'config\nativephp.env'

if (-not (Test-Path -LiteralPath $nativeEnvFile)) {
    Write-Host 'No config\nativephp.env - skipping native env merge.' -ForegroundColor Yellow
    exit 0
}

if (-not (Test-Path -LiteralPath $envFile)) {
    Copy-Item -LiteralPath (Join-Path $ProjectRoot '.env.example') -Destination $envFile
    Write-Host 'Created .env from .env.example' -ForegroundColor Yellow
}

function Format-EnvLine {
    param(
        [string]$Key,
        [string]$Value
    )

    return $Key + '=' + $Value
}

function Parse-EnvLines {
    param([string[]]$Lines)

    $pairs = [ordered]@{}

    foreach ($line in $Lines) {
        $trimmed = $line.Trim()

        if ($trimmed -eq '' -or $trimmed.StartsWith('#')) {
            continue
        }

        $eq = $trimmed.IndexOf('=')

        if ($eq -lt 1) {
            continue
        }

        $key = $trimmed.Substring(0, $eq).Trim()
        $value = $trimmed.Substring($eq + 1)
        $pairs[$key] = $value
    }

    return $pairs
}

$envLines = Get-Content -LiteralPath $envFile -Encoding utf8
$nativeLines = Get-Content -LiteralPath $nativeEnvFile -Encoding utf8
$merged = Parse-EnvLines $envLines
$overrides = Parse-EnvLines $nativeLines

if ($overrides.Contains('MOBILE_API_BASE_URL') -and $overrides['MOBILE_API_BASE_URL'] -eq 'auto') {
    $setupScript = Join-Path $PSScriptRoot 'setup-android-api-access.ps1'
    $resolved = & $setupScript -ProjectRoot $ProjectRoot -SetupFirewall
    $overrides['MOBILE_API_BASE_URL'] = [string]$resolved
}

foreach ($key in $overrides.Keys) {
    $merged[$key] = $overrides[$key]
}

$output = New-Object System.Collections.Generic.List[string]
$writtenKeys = @{}

foreach ($line in $envLines) {
    $trimmed = $line.Trim()

    if ($trimmed -eq '' -or $trimmed.StartsWith('#')) {
        $output.Add($line)
        continue
    }

    $eq = $trimmed.IndexOf('=')

    if ($eq -lt 1) {
        $output.Add($line)
        continue
    }

    $key = $trimmed.Substring(0, $eq).Trim()

    if ($writtenKeys.ContainsKey($key)) {
        continue
    }

    if ($merged.Contains($key)) {
        $value = [string]$merged[$key]
        $output.Add((Format-EnvLine -Key $key -Value $value))
        $writtenKeys[$key] = $true
        $merged.Remove($key)
    }
}

foreach ($key in $merged.Keys) {
    if (-not $writtenKeys.ContainsKey($key)) {
        if ($output.Count -gt 0 -and $output[$output.Count - 1] -ne '') {
            $output.Add('')
        }

        $value = [string]$merged[$key]
        $output.Add((Format-EnvLine -Key $key -Value $value))
    }
}

Set-Content -LiteralPath $envFile -Value $output -Encoding utf8
Write-Host 'Merged config\nativephp.env into .env' -ForegroundColor Green

foreach ($key in $overrides.Keys) {
    $value = [string]$overrides[$key]
    Write-Host ('  ' + (Format-EnvLine -Key $key -Value $value)) -ForegroundColor DarkGray
}
