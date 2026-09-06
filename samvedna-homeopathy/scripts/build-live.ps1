<#
.SYNOPSIS
    Build the static site and assemble the cPanel deploy folder (live/).

.DESCRIPTION
    The host is PHP-only - no Node - so the Next.js app is exported to static
    HTML and served by Apache, with core-php sharing the SAME docroot. That is
    why next.config.ts has no rewrites: /admin, /api and /uploads are real paths.

    live/ mirrors the docroot exactly. Upload its CONTENTS to
    /home3/extrabit/<domain>/ and the site is live.

        live/
          index.html  _next/  images/  blog/<slug>/  blog-view/  404.html
          admin/  api/  includes/  config/  migrations/  uploads/  router.php
          .htaccess

    Steps:
      1. Checks PHP_ADMIN_URL actually serves content. Blogs, doctors and video
         testimonials have no bundled fallback, so building against a dead API
         would silently ship a site missing those three sections.
      2. `npm run build` with PHP_ADMIN_URL + NEXT_PUBLIC_SITE_URL exported. Both are
         read at BUILD time: content is baked into the HTML and the site URL into
         every canonical tag.
      3. Wipes live/ and copies out/ + core-php into it.
      4. Copies core-php/.env.production to live/.env (config/config.php has a
         built-in .env parser) and the docroot .htaccess from
         deploy/htaccess-site.conf.

.EXAMPLE
    powershell -File scripts\build-live.ps1
    powershell -File scripts\build-live.ps1 -SkipBuild
    powershell -File scripts\build-live.ps1 -Zip
    powershell -File scripts\build-live.ps1 -AllowEmptyContent
#>
[CmdletBinding()]
param(
    [switch]$SkipBuild,
    [switch]$Zip,
    # Override the build-time content source from secrets.ps1. Needed whenever a
    # NEW admin-managed section ships: its /api endpoint does not exist on the
    # live server until this very folder is uploaded, so the first build of that
    # release has to read from a source that already has it (usually the local
    # PHP server, http://127.0.0.1:8000).
    [string]$ContentSource,
    # Ship even when the content API returned nothing. Blogs, doctors and video
    # testimonials come only from the admin panel - there is no bundled fallback -
    # so an unreachable PHP server exports a site with those sections missing.
    # Without this switch that is a hard failure rather than a silent one.
    [switch]$AllowEmptyContent
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
$out  = Join-Path $root "out"
$live = Join-Path $root "live"

function Info($m) { Write-Host "  $m" -ForegroundColor Cyan }
function Ok($m)   { Write-Host "OK  $m" -ForegroundColor Green }
function Warn($m) { Write-Host "!   $m" -ForegroundColor Yellow }

# Load secrets (site URL, build-time content source, DB creds).
$secrets = Join-Path $PSScriptRoot "secrets.ps1"
if (-not (Test-Path $secrets)) {
    throw "scripts/secrets.ps1 not found - copy scripts/secrets.example.ps1 to it and fill in the cPanel values."
}
. $secrets
foreach ($v in @("SITE_URL","PHP_ADMIN_URL")) {
    if ([string]::IsNullOrWhiteSpace((Get-Variable -Name $v -ValueOnly -ErrorAction SilentlyContinue))) {
        throw "secrets.ps1 is missing a value for `$$v."
    }
}
# -ContentSource wins over secrets.ps1 for this run only.
if (-not [string]::IsNullOrWhiteSpace($ContentSource)) {
    $PHP_ADMIN_URL = $ContentSource.TrimEnd('/')
    Warn "Content source overridden: $PHP_ADMIN_URL"
}

# Runtime DB config now ships as live/.env, sourced from core-php/.env.production.
$envProd = Join-Path $root "core-php\.env.production"
if (-not (Test-Path $envProd)) {
    throw "core-php/.env.production not found - copy core-php/.env.example to it and fill in the cPanel DB values."
}
if ((Get-Content $envProd -Raw) -match "CHANGE_ME") {
    throw "core-php/.env.production still has CHANGE_ME placeholders - fill in the real cPanel values."
}

# robocopy wrapper: exit codes 0-7 are success, only >= 8 is a real failure.
function Copy-Tree {
    param([string]$Src, [string]$Dst, [string[]]$ExtraArgs = @())
    if (-not (Test-Path $Src)) { throw "source not found: $Src" }
    $rcArgs = @($Src, $Dst, "/E", "/NFL", "/NDL", "/NJH", "/NJS") + $ExtraArgs
    robocopy @rcArgs | Out-Null
    if ($LASTEXITCODE -ge 8) { throw "robocopy failed ($Src -> $Dst), code $LASTEXITCODE" }
}

# 1. Content sanity check - BEFORE the build.
# Blogs, doctors and video testimonials render ONLY from the admin API; there is
# no bundled fallback. Checking first turns "PHP server not running" into one
# clear sentence here, instead of a confusing Next.js error several minutes in.
if (-not $SkipBuild) {
    Info "Checking content source $PHP_ADMIN_URL ..."
    $counts = @{}
    $reachable = $true
    foreach ($ep in @("blogs", "doctors", "testimonials", "conditions")) {
        try {
            $rows = Invoke-RestMethod -Uri "$PHP_ADMIN_URL/api/$ep.php" -TimeoutSec 15
            $counts[$ep] = @($rows).Count
        } catch {
            $reachable = $false
            $counts[$ep] = 0
        }
    }

    if (-not $reachable) {
        if ($AllowEmptyContent) {
            Warn "$PHP_ADMIN_URL is unreachable - building with all three sections empty."
        } else {
            throw @"
Refusing to build: $PHP_ADMIN_URL is unreachable.

Blogs, doctors and video testimonials come only from the admin panel, so this
export would ship with those three sections missing entirely - and that is what
search engines would index.

Start the content source first, in a second terminal:
    npm run dev:php

then re-run this script. Verify with:
    curl $PHP_ADMIN_URL/api/blogs.php

To publish a site with empty sections anyway:
    powershell -File scripts\build-live.ps1 -AllowEmptyContent
"@
        }
    } else {
        Ok ("Content source: {0} blogs, {1} doctors, {2} testimonials, {3} conditions." -f $counts["blogs"], $counts["doctors"], $counts["testimonials"], $counts["conditions"])
        if ($counts["conditions"] -eq 0) {
            Warn "No conditions returned - the homepage 'Conditions we support' grid will be MISSING from this export."
            Warn "  If this source has not had the conditions migration run yet, build from one that has:"
            Warn "    powershell -File scripts\build-live.ps1 -ContentSource http://127.0.0.1:8000"
        }
        $totalRows = $counts["blogs"] + $counts["doctors"] + $counts["testimonials"]
        if ($totalRows -eq 0) {
            if ($AllowEmptyContent) {
                Warn "The admin panel has no published content - all three sections will be empty."
            } else {
                throw @"
Refusing to build: the admin panel at $PHP_ADMIN_URL has no published content.

All three admin-managed sections (parent stories, medical team, blog) would be
missing from the site. Add content in the admin panel first, remembering that
blog posts must be set to Published - drafts are excluded from the API.

To publish a site with empty sections anyway:
    powershell -File scripts\build-live.ps1 -AllowEmptyContent
"@
            }
        }
    }
}

# 2. Build
if (-not $SkipBuild) {
    Info "Building static export ..."
    Info "  content from : $PHP_ADMIN_URL"
    Info "  canonical url: $SITE_URL"
    $prevPhp  = $env:PHP_ADMIN_URL
    $prevSite = $env:NEXT_PUBLIC_SITE_URL
    $env:PHP_ADMIN_URL        = $PHP_ADMIN_URL
    $env:NEXT_PUBLIC_SITE_URL = $SITE_URL
    Push-Location $root
    try {
        npm run build
        if ($LASTEXITCODE -ne 0) { throw "npm run build failed (exit $LASTEXITCODE)." }
    } finally {
        Pop-Location
        $env:PHP_ADMIN_URL        = $prevPhp
        $env:NEXT_PUBLIC_SITE_URL = $prevSite
    }
}
if (-not (Test-Path (Join-Path $out "index.html"))) {
    throw "out/index.html missing - the static export did not run. (Remove -SkipBuild?)"
}
$blogCount = @(Get-ChildItem (Join-Path $out "blog") -Directory -ErrorAction SilentlyContinue).Count
Ok "Static export ready ($blogCount blog pages prebuilt)."

# 3. Wipe live/
Info "Cleaning live/ ..."
if (Test-Path $live) { Get-ChildItem -LiteralPath $live -Force | Remove-Item -Recurse -Force }
else { New-Item -ItemType Directory -Path $live | Out-Null }

# 4. Static export -> docroot root
Info "Copying static export -> live/ ..."
Copy-Tree $out $live

# 5. PHP backend -> same docroot.
#    robocopy /XF matches by FILE NAME at every level, so it cannot be used to
#    drop just the top-level index.php - that would also strip admin/index.php
#    and every admin/<resource>/index.php. Copy everything, then remove the two
#    root files that clash with the export.
Info "Copying core-php -> live/ ..."
Copy-Tree (Join-Path $root "core-php") $live @(
    "/XF","start-admin-server.bat","README.md",".gitignore","config.local.php",
    ".env",".env.example",".env.production"
)
# core-php/index.php only redirects to /admin and would fight the exported
# index.html; core-php/.htaccess is the PHP-only ruleset, and the docroot needs
# the combined one from deploy/htaccess-site.conf (written below).
Remove-Item (Join-Path $live "index.php") -Force -ErrorAction SilentlyContinue

Info "Writing docroot .htaccess ..."
Copy-Item (Join-Path $root "deploy\htaccess-site.conf") (Join-Path $live ".htaccess") -Force
Ok "Files copied."

# 6. PHP env config. config.php reads config.local.php, then .env, then
#    getenv(), then hardcoded root/no-password defaults - this file is what
#    keeps the live site off those defaults.
Info "Copying core-php/.env.production -> live/.env ..."
Copy-Item $envProd (Join-Path $live ".env") -Force
Ok "Env config shipped as live/.env."

# 7. Sanity checks - these are the failures that only show up in production.
foreach ($must in @("index.html","404.html",".htaccess",".env","router.php","api\leads.php","admin\index.php","uploads\.htaccess","blog-view\index.html")) {
    if (-not (Test-Path (Join-Path $live $must))) { throw "live/$must missing - deploy would be broken." }
}
Ok "Sanity checks passed."

# 8. Optional zip
if ($Zip) {
    $zipPath = Join-Path $root "live.zip"
    if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
    Info "Zipping live/ contents -> live.zip ..."
    Compress-Archive -Path (Join-Path $live "*") -DestinationPath $zipPath -Force
    Ok "Created $zipPath"
}

$size = (Get-ChildItem $live -Recurse -File -Force | Measure-Object -Property Length -Sum).Sum
Ok ("Done. live/ = {0:N1} MB. Upload its CONTENTS to the $SITE_URL docroot." -f ($size/1MB))
Write-Host ""
Warn "In cPanel File Manager enable 'Show Hidden Files' so .htaccess is visible."
Warn "chmod uploads/ to 755 so admin image uploads work."
Warn "First deploy only: visit /admin, log in as admin/admin123, and change that password."

# robocopy exits 1-7 on SUCCESS, and PowerShell would otherwise return that as
# the script's exit code - making a clean deploy look like a failure to the
# shell (and to any CI wrapping it). Every real error above throws instead.
exit 0
