<#
.SYNOPSIS
    Start the Samvedna CMS - MySQL, the Core PHP API, and the Next.js frontend.

.DESCRIPTION
    Checks the toolchain, brings MySQL up if it is not already listening, starts
    the PHP API and the Next dev server, waits until both actually answer, and
    prints the URLs. Ctrl+C stops everything it started.

    Ports are checked before use: if the preferred port is busy the script walks
    forward to the next free one rather than dying with EADDRINUSE, and the
    frontend is told where the backend actually landed.

.PARAMETER Seed
    Run migrations and seed the demo walkthrough before starting.

.PARAMETER Fresh
    Drop every cms_* table and rebuild the demo from scratch. Only the CMS
    tables are dropped - the marketing site's blogs/doctors/testimonials are
    left alone.

.PARAMETER Build
    Serve a production build instead of the dev server.

.EXAMPLE
    .\start-cms.ps1
.EXAMPLE
    .\start-cms.ps1 -Fresh
#>
[CmdletBinding()]
param(
    [switch] $Seed,
    [switch] $Fresh,
    [switch] $Build,
    [int]    $BackendPort  = 8001,
    [int]    $FrontendPort = 3001,
    [switch] $SkipMysql,
    [switch] $Stop
)

$ErrorActionPreference = 'Stop'
$root     = $PSScriptRoot
$backend  = Join-Path $root 'backend'
$frontend = Join-Path $root 'frontend'
$started  = @()   # processes we spawned, so Ctrl+C can clean them up

# ---------------------------------------------------------------- helpers --

function Say([string]$msg, [string]$colour = 'Gray') { Write-Host $msg -ForegroundColor $colour }
function Step([string]$msg) { Write-Host ''; Write-Host "==> $msg" -ForegroundColor Cyan }
function Warn([string]$msg) { Write-Host "  ! $msg" -ForegroundColor Yellow }
function Die([string]$msg)  { Write-Host ''; Write-Host "  x $msg" -ForegroundColor Red; exit 1 }

function Test-Port([int]$port) {
    return [bool](Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue)
}

# Walk forward from $preferred until a free port turns up.
function Find-FreePort([int]$preferred, [int]$tries = 12) {
    for ($p = $preferred; $p -lt ($preferred + $tries); $p++) {
        if (-not (Test-Port $p)) { return $p }
    }
    Die "No free port in $preferred..$($preferred + $tries - 1)."
}

function Wait-Until([scriptblock]$check, [int]$seconds, [string]$what) {
    for ($i = 0; $i -lt $seconds; $i++) {
        if (& $check) { return $true }
        Start-Sleep -Seconds 1
    }
    Warn "Timed out after ${seconds}s waiting for $what."
    return $false
}

function Test-Url([string]$url) {
    try {
        Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 3 | Out-Null
        return $true
    } catch {
        # A 4xx/5xx still proves something is listening and speaking HTTP.
        return ($null -ne $_.Exception.Response)
    }
}

# ------------------------------------------------------------------ stop ---

# A hard kill (or a crashed terminal) skips the finally block below and leaves
# the servers listening. `-Stop` clears whatever is still holding the CMS ports
# so the next run starts clean instead of walking to 8002/3003/...
if ($Stop) {
    Step 'Stopping anything on the CMS ports'
    $found = $false
    foreach ($port in @($BackendPort, $FrontendPort, ($BackendPort + 1), ($FrontendPort + 1))) {
        $conns = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
        foreach ($conn in $conns) {
            $proc = Get-Process -Id $conn.OwningProcess -ErrorAction SilentlyContinue
            $name = 'unknown'
            if ($proc) { $name = $proc.ProcessName }
            & taskkill /F /PID $conn.OwningProcess /T 2>&1 | Out-Null
            Say "  stopped $port -> PID $($conn.OwningProcess) ($name)"
            $found = $true
        }
    }
    if (-not $found) { Say '  nothing was running' }
    Say '  MySQL was left alone.' 'DarkGray'
    exit 0
}

# ------------------------------------------------------------ toolchain ----

Step 'Checking the toolchain'
foreach ($tool in @('php', 'node', 'npm')) {
    $cmd = Get-Command $tool -ErrorAction SilentlyContinue
    if (-not $cmd) { Die "$tool is not on PATH. Install it, or open a shell where it is." }
    Say ("  {0,-5} {1}" -f $tool, $cmd.Source)
}
if (-not (Test-Path (Join-Path $frontend 'node_modules'))) {
    Step 'Installing frontend dependencies (first run)'
    Push-Location $frontend
    try { & npm install --no-audit --no-fund } finally { Pop-Location }
}

# ---------------------------------------------------------------- MySQL ----

if (-not $SkipMysql) {
    Step 'MySQL'
    if (Test-Port 3306) {
        Say '  already listening on 3306' 'Green'
    } else {
        $mysqld = @(
            'C:\xampp\mysql\bin\mysqld.exe',
            'D:\xampp\mysql\bin\mysqld.exe',
            'E:\xampp\mysql\bin\mysqld.exe'
        ) | Where-Object { Test-Path $_ } | Select-Object -First 1

        if (-not $mysqld) {
            Warn 'Could not find XAMPP mysqld.exe - start MySQL yourself, or pass -SkipMysql.'
        } else {
            # A half-dead mysqld keeps the data files locked and every new
            # instance dies with "ibdata1 must be writable". Clear those first.
            $stale = Get-Process -Name mysqld -ErrorAction SilentlyContinue
            if ($stale) {
                Warn "$($stale.Count) mysqld process(es) already running but not serving - clearing."
                foreach ($proc in $stale) {
                    try {
                        Stop-Process -Id $proc.Id -Force -ErrorAction Stop
                    } catch {
                        Warn "PID $($proc.Id) will not die without admin rights."
                        Warn 'Run this in an ELEVATED PowerShell, then retry:'
                        Warn "    taskkill /F /PID $($proc.Id)"
                        Warn '(or simply reboot - that clears it too)'
                    }
                }
                Start-Sleep -Seconds 3
            }

            $log = Join-Path $env:TEMP 'samvedna-mysqld.log'
            $ini = Join-Path (Split-Path $mysqld) 'my.ini'
            Say "  starting $mysqld"
            Start-Process -FilePath $mysqld `
                -ArgumentList "--defaults-file=$ini", "--log-error=$log" `
                -WindowStyle Hidden

            if (Wait-Until { Test-Port 3306 } 40 'MySQL') {
                Say '  MySQL is up on 3306' 'Green'
            } else {
                Warn "MySQL did not start. Error log: $log"
                if (Test-Path $log) {
                    Get-Content $log -Tail 6 | ForEach-Object { Write-Host "      $_" -ForegroundColor DarkYellow }
                }
                Warn 'If InnoDB reports corruption, see the recovery notes in README.md.'
            }
        }
    }
}

# ------------------------------------------------------------ migrations ---

if ($Seed -or $Fresh) {
    Step 'Migrations'
    $migrateArgs = @((Join-Path $backend 'migrations\migrate.php'))
    if ($Fresh) { $migrateArgs += '--fresh' }
    $migrateArgs += '--seed-demo'
    & php @migrateArgs
    if ($LASTEXITCODE -ne 0) { Die 'Migration failed - see the output above.' }
}

# --------------------------------------------------------------- backend ---

Step 'Backend (Core PHP)'
$apiPort = Find-FreePort $BackendPort
if ($apiPort -ne $BackendPort) { Warn "Port $BackendPort was busy - using $apiPort instead." }

# Start-Process joins -ArgumentList with spaces WITHOUT quoting each element, so
# an array here would split this project's path at "EB projects". Build one
# pre-quoted command line instead.
$router  = Join-Path $backend 'router.php'
$apiArgs = "-S 127.0.0.1:$apiPort -t `"$backend`" `"$router`""
$api = Start-Process -FilePath 'php' -ArgumentList $apiArgs `
    -WorkingDirectory $root -PassThru -WindowStyle Hidden
$started += $api

if (Wait-Until { Test-Url "http://127.0.0.1:$apiPort/" } 20 'the PHP API') {
    Say "  API ready  http://127.0.0.1:$apiPort/api" 'Green'
} else {
    Warn 'The API did not answer - the frontend will show a connection error until it does.'
}

# -------------------------------------------------------------- frontend ---

Step 'Frontend (Next.js)'
$webPort = Find-FreePort $FrontendPort
if ($webPort -ne $FrontendPort) { Warn "Port $FrontendPort was busy - using $webPort instead." }

# next.config.ts proxies /api here, so the two halves stay same-origin and the
# PHP session cookie works with no token plumbing.
$env:PHP_API_URL = "http://127.0.0.1:$apiPort"

if ($Build) {
    Say '  building for production...'
    Push-Location $frontend
    try {
        & npx next build
        if ($LASTEXITCODE -ne 0) { Die 'next build failed.' }
    } finally { Pop-Location }
    $nextCmd = "next start -p $webPort"
} else {
    $nextCmd = "next dev -p $webPort"
}

# `npx` resolves to npx.ps1 on some setups, which Start-Process cannot execute
# directly. Going through cmd.exe picks up npx.cmd and gives taskkill /T a
# process tree it can actually tear down.
$web = Start-Process -FilePath 'cmd.exe' -ArgumentList "/c npx $nextCmd" `
    -WorkingDirectory $frontend -PassThru -WindowStyle Hidden
$started += $web

if (Wait-Until { Test-Url "http://127.0.0.1:$webPort/login" } 120 'the Next dev server') {
    Say "  Web ready  http://127.0.0.1:$webPort" 'Green'
} else {
    Warn 'Next did not answer in time. It may still be compiling - try the URL in a moment.'
}

# ------------------------------------------------------------------ run ----

Write-Host ''
Write-Host '  Samvedna CMS is running' -ForegroundColor Green
Write-Host ''
Write-Host "    Web  http://127.0.0.1:$webPort"
Write-Host "    API  http://127.0.0.1:$apiPort/api"
Write-Host ''
Write-Host '    Sign in: founder / senior / casedoctor / coordinator  |  password samvedna123' -ForegroundColor DarkGray
Write-Host ''
Write-Host '  Ctrl+C to stop both.' -ForegroundColor DarkGray
Write-Host ''

try {
    while ($true) {
        Start-Sleep -Seconds 2
        foreach ($proc in $started) {
            if ($proc.HasExited) {
                Warn "A process exited (PID $($proc.Id)). Shutting the rest down."
                return
            }
        }
    }
} finally {
    Write-Host ''
    Step 'Stopping'
    foreach ($proc in $started) {
        if (-not $proc.HasExited) {
            # /T so npx's child node process goes too, not just the launcher.
            & taskkill /F /PID $proc.Id /T 2>&1 | Out-Null
            Say "  stopped PID $($proc.Id)"
        }
    }
    Say '  MySQL was left running.' 'DarkGray'
}
