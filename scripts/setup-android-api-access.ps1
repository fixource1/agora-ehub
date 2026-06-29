# Resolve a Docker API URL that the Android emulator can reach (WSL2 + Windows).
param(
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot),
    [int]$Port = 8000,
    [switch]$UpdateNativeEnv,
    [switch]$SetupFirewall
)

$ErrorActionPreference = 'Stop'

function Test-HttpOk {
    param([string]$Url)

    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 4
        return $response.StatusCode -ge 200 -and $response.StatusCode -lt 500
    } catch {
        return $false
    }
}

function Get-WslIpAddress {
    try {
        $raw = (wsl -e hostname -I 2>$null)

        if (-not $raw) {
            return $null
        }

        return ($raw.Trim() -split '\s+')[0]
    } catch {
        return $null
    }
}

function Get-LanIpAddress {
    try {
        $address = Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue |
            Where-Object {
                $_.IPAddress -notlike '127.*' -and
                $_.IPAddress -notlike '169.254.*' -and
                $_.PrefixOrigin -ne 'WellKnown'
            } |
            Select-Object -First 1

        if ($address) {
            return $address.IPAddress
        }
    } catch {
        return $null
    }

    return $null
}

function Test-EmulatorHttpOk {
    param([string]$Url)

    $adb = Get-Command adb -ErrorAction SilentlyContinue

    if (-not $adb) {
        return $false
    }

    $devices = (& adb devices 2>$null | Select-String 'device$')

    if (-not $devices) {
        return $false
    }

    $escaped = $Url.Replace('"', '\"')
    $code = & adb shell "curl -s -o /dev/null -w '%{http_code}' `"$escaped`"" 2>$null

    return "$code".Trim() -eq '200'
}

function Ensure-FirewallRule {
    param([int]$ListenPort)

    if (-not $SetupFirewall) {
        return
    }

    $ruleName = "AGORA e-Hub Docker $ListenPort"

    try {
        $existing = Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue

        if ($existing) {
            Write-Host "Firewall rule already exists: $ruleName" -ForegroundColor DarkGray
            return
        }

        New-NetFirewallRule `
            -DisplayName $ruleName `
            -Direction Inbound `
            -Action Allow `
            -Protocol TCP `
            -LocalPort $ListenPort `
            -Profile Private, Domain `
            -ErrorAction Stop | Out-Null

        Write-Host "Added firewall rule for TCP $ListenPort" -ForegroundColor Green
    } catch {
        Write-Host "Could not add firewall rule (run PowerShell as Administrator if needed)." -ForegroundColor Yellow
    }
}

$healthPath = "/api/v1/resources"
$wslIp = Get-WslIpAddress
$lanIp = Get-LanIpAddress

Write-Host 'Checking Docker API reachability for Android emulator...' -ForegroundColor Cyan

$windowsChecks = @(
    @{ Label = 'Windows localhost'; Base = "http://127.0.0.1:$Port" }
)

if ($wslIp) {
    $windowsChecks += @{ Label = 'WSL IP'; Base = "http://${wslIp}:$Port" }
}

if ($lanIp) {
    $windowsChecks += @{ Label = 'LAN IP'; Base = "http://${lanIp}:$Port" }
}

foreach ($check in $windowsChecks) {
    $url = $check.Base + $healthPath
    $ok = Test-HttpOk $url
    $status = if ($ok) { 'OK' } else { 'FAIL' }
    Write-Host ('  [{0}] {1}' -f $status, $url) -ForegroundColor $(if ($ok) { 'Green' } else { 'DarkGray' })
}

$emulatorCandidates = @()

if ($wslIp) {
    $emulatorCandidates += @{ Label = 'WSL IP (recommended)'; Base = "http://${wslIp}:$Port" }
}

$emulatorCandidates += @(
    @{ Label = 'Android host alias'; Base = "http://10.0.2.2:$Port" }
)

if ($lanIp) {
    $emulatorCandidates += @{ Label = 'LAN IP'; Base = "http://${lanIp}:$Port" }
}

$resolvedBase = $null

foreach ($candidate in $emulatorCandidates) {
    $url = $candidate.Base + $healthPath

    if (Test-EmulatorHttpOk $url) {
        $resolvedBase = $candidate.Base
        Write-Host "Emulator can reach API via $($candidate.Label): $resolvedBase" -ForegroundColor Green
        break
    }
}

if (-not $resolvedBase) {
  foreach ($candidate in $emulatorCandidates) {
        $url = $candidate.Base + $healthPath

        if (Test-HttpOk $url) {
            $resolvedBase = $candidate.Base
            Write-Host "Using $($candidate.Label) from Windows (emulator not tested): $resolvedBase" -ForegroundColor Yellow
            break
        }
    }
}

if (-not $resolvedBase -and $wslIp) {
    $resolvedBase = "http://${wslIp}:$Port"
    Write-Host "Falling back to WSL IP (common fix for WSL2 + emulator): $resolvedBase" -ForegroundColor Yellow
}

if (-not $resolvedBase) {
    $resolvedBase = "http://10.0.2.2:$Port"
    Write-Host "Falling back to 10.0.2.2. If the emulator still fails, run Docker in WSL and retry." -ForegroundColor Yellow
}

Ensure-FirewallRule -ListenPort $Port

if ($UpdateNativeEnv) {
    $nativeEnvFile = Join-Path $ProjectRoot 'config\nativephp.env'

    if (Test-Path -LiteralPath $nativeEnvFile) {
        $lines = Get-Content -LiteralPath $nativeEnvFile -Encoding utf8
        $updated = $false
        $output = New-Object System.Collections.Generic.List[string]

        foreach ($line in $lines) {
            if ($line -match '^\s*MOBILE_API_BASE_URL=') {
                $output.Add('MOBILE_API_BASE_URL=' + $resolvedBase)
                $updated = $true
            } else {
                $output.Add($line)
            }
        }

        if (-not $updated) {
            $output.Add('MOBILE_API_BASE_URL=' + $resolvedBase)
        }

        Set-Content -LiteralPath $nativeEnvFile -Value $output -Encoding utf8
        Write-Host "Updated config\nativephp.env -> MOBILE_API_BASE_URL=$resolvedBase" -ForegroundColor Green
    }
}

Write-Output $resolvedBase
