<#
.SYNOPSIS
  Build the 240p preview renditions the footer video wall plays in its strip.

  The wall tiles play "<name>-240p.webm" (VP9, smallest) with "<name>-240p.mp4" (H.264) as
  the fallback for browsers without WebM — both muted and tiny — and only the lightbox loads
  the full-size "<name>.mp4" after a click. The theme (inc/template-tags.php,
  dq_video_previews) looks for the -240p siblings next to each clip and falls back to the
  full file when they are missing — so run this after uploading a new video to the Media
  Library (or drop the -240p files next to it on the server).

.USAGE
  .\make-video-previews.ps1                 scan the theme's assets/video and the local WP uploads
  .\make-video-previews.ps1 -Path C:\clips  scan another folder (recursively)
  .\make-video-previews.ps1 -Force          re-encode even if the -240p files already exist
  .\make-video-previews.ps1 -NoWebm         H.264 MP4 previews only (skip the VP9 WebM pass)

  Needs ffmpeg on PATH (winget install Gyan.FFmpeg).
#>
param(
    [string[]]$Path,
    [switch]$Force,
    [switch]$NoWebm
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $Path) {
    $Path = @(
        (Join-Path $root 'wp-content\themes\dynamiqes\assets\video'),
        (Join-Path $root '.local-wp\www\wp-content\uploads')
    )
}
if (-not (Get-Command ffmpeg -ErrorAction SilentlyContinue)) { throw 'ffmpeg not found on PATH (winget install Gyan.FFmpeg)' }

$sources = foreach ($p in $Path) {
    if (Test-Path -LiteralPath $p) {
        Get-ChildItem -LiteralPath $p -Recurse -File |
            Where-Object { $_.Extension -match '^\.(mp4|webm|mov|m4v)$' -and $_.BaseName -notmatch '-240p$' }
    }
}

function Encode-Preview([System.IO.FileInfo]$src, [string]$out, [string[]]$codecArgs) {
    if (-not $Force -and (Test-Path -LiteralPath $out) -and (Get-Item -LiteralPath $out).LastWriteTime -ge $src.LastWriteTime) {
        Write-Host ("skip   {0} (up to date)" -f (Split-Path -Leaf $out))
        return
    }
    Write-Host ("encode {0} -> {1}" -f $src.Name, (Split-Path -Leaf $out))
    # 240p tall, even width, 24 fps; muted (the strip is always muted); ~300 kbps cap.
    & ffmpeg -hide_banner -loglevel error -y -i $src.FullName `
        -vf 'scale=-2:240:flags=lanczos' -r 24 -an @codecArgs $out
    if ($LASTEXITCODE -ne 0) { throw "ffmpeg failed on $($src.Name)" }
    Write-Host ("       {0:N1} MB -> {1:N2} MB" -f ($src.Length / 1MB), ((Get-Item -LiteralPath $out).Length / 1MB))
}

foreach ($src in $sources) {
    $base = Join-Path $src.DirectoryName ($src.BaseName + '-240p')
    # H.264 baseline: every phone decodes it; faststart so playback begins before the download finishes.
    Encode-Preview $src "$base.mp4" @('-c:v', 'libx264', '-preset', 'slow', '-crf', '31', '-maxrate', '320k', '-bufsize', '640k', '-g', '48',
        '-profile:v', 'baseline', '-level', '3.0', '-pix_fmt', 'yuv420p', '-movflags', '+faststart')
    if (-not $NoWebm) {
        # VP9 constrained quality (libvpx needs -b:v alongside -crf when -maxrate is set). Calibrated by SSIM against the
        # H.264 file above: crf 52 / 130k lands at the same quality for ~15% fewer bytes on these screen-recording demos.
        Encode-Preview $src "$base.webm" @('-c:v', 'libvpx-vp9', '-b:v', '130k', '-crf', '52', '-maxrate', '300k', '-bufsize', '600k', '-g', '48',
            '-deadline', 'good', '-cpu-used', '2', '-row-mt', '1', '-pix_fmt', 'yuv420p')
    }
}
