---
description: Ritual penutup sesi koding GEULIS — memeriksa mutu, menulis entri jurnal, lalu commit dengan pesan yang benar.
disable-model-invocation: true
allowed-tools: [Bash, Read, Edit, Write]
---

# Sesi selesai

Jalankan berurutan. **Jangan melompat ke commit sebelum langkah 1 dan 2 hijau.**

## 1. Periksa mutu

```bash
docker exec geulis-php ./vendor/bin/pint
docker exec geulis-php php artisan test
```

Kalau ada uji yang merah: **jangan commit sebagai selesai.** Dua pilihan, tawarkan keduanya ke pengguna:

- perbaiki sekarang, atau
- commit sebagai `wip(...)` dengan badan pesan yang menyebutkan uji mana yang merah dan apa dugaan sebabnya, supaya sesi berikutnya tidak mulai dari nol.

Jangan pernah melaporkan pekerjaan selesai bila ada uji yang merah.

## 2. Tinjau apa yang berubah

```bash
git status --short
git diff --stat
```

Bacakan ringkasannya ke pengguna. Kalau ada berkas yang berubah di luar sasaran sesi, sebutkan — sering kali itu perubahan tak sengaja.

Periksa juga:
- Variabel `.env` baru sudah masuk `.env.example`?
- Perubahan pada aturan adaptasi sudah punya kasus uji?
- Layar baru sudah dicoba pada lebar 360 px?

## 3. Tulis entri jurnal

Tambahkan entri baru **di paling atas** `docs/JURNAL-SESI.md`, mengikuti format yang sudah ada di berkas itu. Isinya singkat dan jujur:

- tanggal, sprint, sasaran sesi
- apa yang benar-benar selesai
- apa yang **tidak** selesai dan mengapa
- keputusan desain yang diambil beserta alasannya — bagian ini yang paling berharga, karena inilah bahan mentah bagian metode artikel dan penjelasan bagi validator ahli
- apa yang harus dikerjakan sesi berikutnya

Kalau sesi ini mengubah parameter di `config/geulis.php`, catat nilai lama, nilai baru, dan alasannya. Ini wajib.

## 4. Perbarui sprint bila perlu

Kalau sasaran sprint sudah tercapai seluruhnya, perbarui `docs/SPRINT-SEKARANG.md` ke sprint berikutnya (ambil dari `docs/SPRINT-BRIEF.md`). Kalau belum, tandai butir mana yang sudah selesai.

## 5. Commit

Satu commit, satu maksud. Kalau sesi ini menyentuh dua hal yang tidak berhubungan, buat dua commit.

```
<tipe>(<lingkup>): <ringkasan huruf kecil, tanpa titik>

<mengapa, bukan apa>

Sprint: <nomor>
```

Tipe: `feat` `fix` `test` `refactor` `docs` `chore` `wip`
Lingkup: `mesin` `motif` `riset` `konten` `auth` `guru` `siswa` `db` `docker` `ui`

**Susun pesannya sendiri, jangan tanya pengguna** — kamu yang tahu apa yang berubah. Tunjukkan pesannya sebelum commit, dan lakukan commit kecuali pengguna keberatan.

Terakhir, laporkan dalam dua kalimat: apa yang tercapai, dan apa yang menunggu sesi berikutnya.
