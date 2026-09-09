# Hook SessionEnd versi PowerShell.
#
# Pakai berkas ini bila Git for Windows (Git Bash) TIDAK terpasang, sehingga
# hook Claude Code berjalan di PowerShell. Ganti perintah pada .claude/settings.json
# menjadi:
#
#   "command": "powershell -NoProfile -ExecutionPolicy Bypass -File \"$CLAUDE_PROJECT_DIR/.claude/hooks/commit-sesi.ps1\""

$ErrorActionPreference = 'SilentlyContinue'

$proyek = if ($env:CLAUDE_PROJECT_DIR) { $env:CLAUDE_PROJECT_DIR } else { (Get-Location).Path }
Set-Location $proyek
$peringatan = Join-Path $proyek '.claude\PERINGATAN-GIT.txt'

git rev-parse --is-inside-work-tree *> $null
if ($LASTEXITCODE -ne 0) { exit 0 }

# Dibersihkan lebih dahulu — lihat penjelasan pada commit-sesi.sh.
Remove-Item $peringatan -ErrorAction SilentlyContinue

$perubahan = git status --porcelain
if (-not $perubahan) { exit 0 }

$nama  = git config user.name
$surel = git config user.email
if (-not $nama -or -not $surel) {
  @(
    "Perubahan TIDAK ter-commit pada $(Get-Date -Format 'yyyy-MM-dd HH:mm')."
    "Sebabnya: git config user.name / user.email belum diatur di repo ini."
    ""
    "Perbaiki dengan:"
    "  git config user.name `"Nama Anda`""
    "  git config user.email `"email@unsil.ac.id`""
    "  git add -A ; git commit"
  ) | Set-Content -Encoding UTF8 $peringatan
  exit 0
}

$stempel = Get-Date -Format 'yyyy-MM-dd HH:mm'
$berkas  = ($perubahan | Measure-Object).Count

git add -A *> $null
git commit -q -m "wip(sesi): simpan otomatis akhir sesi $stempel" -m "Commit jaring pengaman dari hook SessionEnd: $berkas berkas berubah dan belum di-commit saat sesi ditutup. Rapikan pada sesi berikutnya lewat /sesi-selesai." *> $null

exit 0
