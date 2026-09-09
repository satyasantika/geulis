#!/usr/bin/env bash
#
# Hook Stop — pengingat lembut.
#
# Berjalan tiap kali Claude selesai satu giliran. Kalau perubahan yang belum
# di-commit sudah menumpuk melewati ambang, ingatkan. Tidak memblokir apa pun.

set -u

AMBANG_BERKAS=8

PROYEK="${CLAUDE_PROJECT_DIR:-$(pwd)}"
cd "$PROYEK" 2>/dev/null || { echo '{}'; exit 0; }

git rev-parse --is-inside-work-tree >/dev/null 2>&1 || { echo '{}'; exit 0; }

JUMLAH="$(git status --porcelain | wc -l | tr -d ' ')"

if [ "$JUMLAH" -ge "$AMBANG_BERKAS" ]; then
  printf '{"hookSpecificOutput":{"hookEventName":"Stop","systemMessage":"%s berkas belum di-commit. Pertimbangkan commit sekarang selagi maksudnya masih satu — satu commit, satu maksud."}}\n' "$JUMLAH"
else
  echo '{}'
fi

exit 0
