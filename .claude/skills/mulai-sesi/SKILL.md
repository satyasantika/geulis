---
description: Ritual pembuka sesi koding GEULIS — memuat konteks sprint, jurnal sesi lalu, dan keadaan repo, lalu menetapkan sasaran sesi.
disable-model-invocation: true
allowed-tools: [Bash, Read, Grep, Glob]
---

# Mulai sesi

Konteks yang dikumpulkan otomatis:

## Keadaan repo

Cabang dan perubahan tertunda:
!`git status --short --branch 2>/dev/null || echo "(bukan repo git)"`

Sepuluh commit terakhir:
!`git log --oneline -10 2>/dev/null || echo "(belum ada commit)"`

Identitas Git:
!`echo "nama : $(git config user.name || echo '(BELUM DIATUR)')"; echo "surel: $(git config user.email || echo '(BELUM DIATUR)')"`

Peringatan tersisa dari sesi lalu:
!`cat .claude/PERINGATAN-GIT.txt 2>/dev/null || echo "(tidak ada)"`

## Sprint yang berjalan

!`if [ -f docs/SPRINT-SEKARANG.md ]; then cat docs/SPRINT-SEKARANG.md; else echo "(docs/SPRINT-SEKARANG.md belum ada)"; fi`

## Tiga entri terakhir jurnal sesi

!`if [ -f docs/JURNAL-SESI.md ]; then awk '/^## /{n++} n<=3' docs/JURNAL-SESI.md | head -60; else echo "(jurnal belum ada)"; fi`

---

## Yang harus kamu lakukan sekarang

1. **Kalau identitas Git belum diatur, hentikan dan minta pengguna mengaturnya lebih dahulu.** Tanpa itu, hook commit akhir sesi tidak bisa bekerja dan pekerjaan sesi ini berisiko hilang.

2. **Kalau ada commit `wip(sesi)` dari sesi lalu yang belum dirapikan**, tawarkan merapikannya dengan `git commit --amend` atau pesan commit yang benar sebelum menulis kode baru. Riwayat yang rapi adalah bukti penciptaan untuk berkas HKI.

3. **Kalau ada perubahan tertunda yang tidak kamu kenali**, tanya pengguna sebelum menyentuhnya — bisa jadi itu pekerjaan tangan mereka sendiri.

4. **Baca `CLAUDE.md`** bila belum termuat pada sesi ini, terutama bagian 6 (aturan yang tidak bisa ditawar).

5. **Tetapkan sasaran sesi** dalam satu kalimat, ambil dari sprint yang berjalan. Tuliskan ke pengguna dan minta persetujuan sebelum menulis baris kode pertama. Kalau pengguna sudah menyebut sasarannya lewat argumen (`$ARGUMENTS`), pakai itu dan cukup konfirmasi bahwa sasaran itu memang ada di sprint yang berjalan.

Jangan mulai menulis kode sebelum langkah 5 selesai.
