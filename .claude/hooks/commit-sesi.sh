#!/usr/bin/env bash
#
# Hook SessionEnd — jaring pengaman commit akhir sesi.
#
# Hook SessionEnd TIDAK BISA membatalkan penutupan sesi (itu batasan Claude Code),
# jadi hook ini tidak mencoba memaksa. Yang dilakukannya: menyimpan apa pun yang
# masih tercecer sebagai commit "wip" supaya tidak ada pekerjaan yang hilang dan
# riwayat Git tidak pernah bolong.
#
# Ini BUKAN pengganti /sesi-selesai. Commit wip yang menumpuk membuat riwayat
# sulit dibaca, dan riwayat yang sulit dibaca melemahkan berkas HKI.

set -u

PROYEK="${CLAUDE_PROJECT_DIR:-$(pwd)}"
PERINGATAN="$PROYEK/.claude/PERINGATAN-GIT.txt"

cd "$PROYEK" 2>/dev/null || exit 0

# Bukan repo Git — tidak ada yang bisa dikerjakan.
git rev-parse --is-inside-work-tree >/dev/null 2>&1 || exit 0

# Berkas peringatan dari sesi lalu dibersihkan LEBIH DAHULU, sebelum pemeriksaan
# kebersihan repo. Kalau dihapus setelah commit, penghapusannya sendiri membuat
# repo kotor lagi dan sesi berikutnya menghasilkan commit kosong yang mengotori
# riwayat. Berkas ini juga masuk .gitignore, jadi tidak pernah ikut ter-commit.
rm -f "$PERINGATAN" 2>/dev/null

# Tidak ada perubahan — sesi sudah bersih, kemungkinan /sesi-selesai sudah jalan.
if [ -z "$(git status --porcelain)" ]; then
  exit 0
fi

# Identitas Git belum diatur: commit pasti gagal. Tinggalkan catatan, jangan diam.
if [ -z "$(git config user.name)" ] || [ -z "$(git config user.email)" ]; then
  {
    echo "Perubahan TIDAK ter-commit pada $(date +'%Y-%m-%d %H:%M')."
    echo "Sebabnya: git config user.name / user.email belum diatur di repo ini."
    echo
    echo "Perbaiki dengan:"
    echo "  git config user.name \"Nama Anda\""
    echo "  git config user.email \"email@unsil.ac.id\""
    echo "  git add -A && git commit"
  } > "$PERINGATAN"
  exit 0
fi

STEMPEL="$(date +'%Y-%m-%d %H:%M')"
BERKAS="$(git status --porcelain | wc -l | tr -d ' ')"

git add -A >/dev/null 2>&1
git commit -q \
  -m "wip(sesi): simpan otomatis akhir sesi ${STEMPEL}" \
  -m "Commit jaring pengaman dari hook SessionEnd: ${BERKAS} berkas berubah dan belum di-commit saat sesi ditutup. Rapikan pada sesi berikutnya lewat /sesi-selesai, atau gabungkan dengan 'git commit --amend'." \
  >/dev/null 2>&1

exit 0
