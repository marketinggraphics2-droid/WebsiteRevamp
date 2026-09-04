<#
.SYNOPSIS
  Runs the DynamIQ WordPress theme locally with no system-wide installs.
  Portable PHP 8.3 + WordPress + SQLite live in .local-wp/ (gitignored); the theme folder is junction-linked,
  so edits under wp-content/themes/dynamiqes show up immediately.

  .\run-local.ps1            start on http://localhost:8787/  (admin: /wp-admin/, user admin / password admin)
  .\run-local.ps1 -Port 8080 use another port (also updates WP_HOME/WP_SITEURL in wp-config.php)
  .\run-local.ps1 -Setup     (re)build .local-wp from the zips in .local-wp/dl and run the installer
  Stop with Ctrl+C.
#>
param(
    [int]$Port = 8787,
    [switch]$Setup,
    [switch]$NoBrowser
)
$ErrorActionPreference = 'Stop'
$root  = $PSScriptRoot
$local = Join-Path $root '.local-wp'
$php   = Join-Path $local 'php\php.exe'
$ini   = Join-Path $local 'php\php.ini'
$www   = Join-Path $local 'www'
$theme = Join-Path $root 'wp-content\themes\dynamiqes'
$link  = Join-Path $www 'wp-content\themes\dynamiqes'

function Ensure-Junction {
    if (-not (Test-Path $link)) {
        New-Item -ItemType Junction -Path $link -Target $theme | Out-Null
        Write-Host "Linked $link -> $theme"
    }
}

if ($Setup -or -not (Test-Path $php)) {
    $dl = Join-Path $local 'dl'
    foreach ($z in 'php.zip', 'wordpress.zip', 'sqlite-database-integration.zip') {
        if (-not (Test-Path (Join-Path $dl $z))) {
            throw "Missing $dl\$z. Download: PHP NTS x64 zip from https://windows.php.net/download/, https://wordpress.org/latest.zip, https://downloads.wordpress.org/plugin/sqlite-database-integration.zip"
        }
    }
    if (-not (Test-Path $php)) { Expand-Archive (Join-Path $dl 'php.zip') (Join-Path $local 'php') -Force }
    if (-not (Test-Path (Join-Path $www 'wp-load.php'))) {
        Expand-Archive (Join-Path $dl 'wordpress.zip') $local -Force
        Rename-Item (Join-Path $local 'wordpress') 'www'
    }
    $plug = Join-Path $www 'wp-content\plugins\sqlite-database-integration'
    if (-not (Test-Path $plug)) { Expand-Archive (Join-Path $dl 'sqlite-database-integration.zip') (Join-Path $www 'wp-content\plugins') -Force }
    $dbphp = Join-Path $www 'wp-content\db.php'
    if (-not (Test-Path $dbphp)) {
        (Get-Content (Join-Path $plug 'db.copy') -Raw).
            Replace('{SQLITE_IMPLEMENTATION_FOLDER_PATH}', ($plug -replace '\', '/')).
            Replace('{SQLITE_PLUGIN}', 'sqlite-database-integration/load.php') |
            Set-Content $dbphp -Encoding utf8 -NoNewline
    }
    if (-not (Test-Path $ini)) { throw "Missing $ini (kept in .local-wp/php/php.ini)" }
    Ensure-Junction
    & $php -c $ini (Join-Path $local 'bootstrap.php')
    if ($LASTEXITCODE -ne 0) { throw 'bootstrap.php failed' }
    & $php -c $ini (Join-Path $local 'seed.php')
    if ($LASTEXITCODE -ne 0) { throw 'seed.php failed' }
}

Ensure-Junction

# keep WP_HOME/WP_SITEURL in sync with the chosen port
$cfg = Join-Path $www 'wp-config.php'
$text = Get-Content $cfg -Raw
$new  = [regex]::Replace($text, "http://localhost:\d+", "http://localhost:$Port")
if ($new -ne $text) { Set-Content $cfg $new -Encoding utf8 -NoNewline }

if (Test-NetConnection localhost -Port $Port -InformationLevel Quiet -WarningAction SilentlyContinue) {
    Write-Host "Something is already listening on port $Port (WordPress already running?). Open http://localhost:$Port/"
    exit 0
}

Write-Host "DynamIQ local WordPress -> http://localhost:$Port/   (admin: http://localhost:$Port/wp-admin/  admin / admin)"
Write-Host 'Press Ctrl+C to stop.'
if (-not $NoBrowser) { Start-Process "http://localhost:$Port/" }
& $php -c $ini -S "localhost:$Port" -t $www (Join-Path $local 'router.php')
