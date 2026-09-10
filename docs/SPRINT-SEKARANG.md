# Sprint yang sedang berjalan

> Salin isi satu sprint dari `SPRINT-BRIEF.md` ke bawah garis ini saat sprint itu dimulai.
> Tandai `[x]` pada butir yang sudah selesai. Berkas ini dibaca agen koding setiap sesi.

**Sprint aktif:** 3 — Motif Builder
**Mulai:** 2026-09-10
**Target selesai:** Bulan 7

---

## Sasaran

Siswa menyusun urutan perintah transformasi, menjalankannya, dan mendapat skor kemiripan otomatis.

## Butir pekerjaan

- [ ] Kanvas SVG dengan kisi koordinat, ramah sentuh, 360 px
- [ ] Blok perintah: `MOTIF_DASAR`, `TRANSLASI`, `REFLEKSI`, `ROTASI`, `DILATASI`, `ULANGI n KALI`
- [ ] Mesin transformasi JavaScript
- [ ] Rasterisasi 200 × 200, kirim ke server
- [ ] Sambungkan ke `MotifScorer` — IoU, ambang 90, efisiensi terpisah
- [ ] Alat Penanda Motif (bukti dekomposisi)
- [ ] Simpan ke `motif_submissions` + cuplikan SVG
- [ ] Motif sasaran untuk lima pertemuan

## Selesai bila

- [ ] Susunan benar → kemiripan ≥ 90, lolos
- [ ] Susunan lebih panjang tetap lolos, efisiensi lebih rendah
- [ ] Bisa dikerjakan dengan ibu jari di 360 px
- [ ] `buktiCT()` menghasilkan data keempat indikator

## Catatan berjalan

- 2026-09-10 — Sprint 0–2 selesai. Data contoh lokal: `php artisan db:seed --class=DemoSeeder` (guru `guru1`/`password`; siswa NIS `0056781234` PIN `482913`).
- Akun seed: username `admin`, surel `admin@geulis.test`, kata sandi `password` (ganti sebelum dipakai di sekolah).
