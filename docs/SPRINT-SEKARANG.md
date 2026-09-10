# Sprint yang sedang berjalan

> Salin isi satu sprint dari `SPRINT-BRIEF.md` ke bawah garis ini saat sprint itu dimulai.
> Tandai `[x]` pada butir yang sudah selesai. Berkas ini dibaca agen koding setiap sesi.

**Sprint aktif:** 5 — Modul penelitian
**Mulai:** 2026-09-10
**Target selesai:** Bulan 8

---

## Sasaran

Validator mengisi lembar lewat tautan, rekap Aiken's V terbit otomatis, data siap diekspor.

## Butir pekerjaan

- [ ] Instrumen validasi: empat aspek, ±28 butir, skala 1–5, saran per butir
- [ ] Tautan bertanda untuk validator — tanpa akun
- [ ] Layar V-01 lembar validasi, simpan sebagian
- [ ] `AikenCalculator` + layar V-02 rekap V per butir, aspek, keseluruhan
- [ ] Ekspor rekap saran sebagai daftar tugas revisi
- [ ] Angket respons siswa & guru setelah pertemuan terakhir
- [ ] Layar O-01 observasi ramah HP, jalan tanpa sinyal
- [ ] Layar P-01 kelengkapan data per kelas
- [ ] Layar P-02 analitik N-Gain + deskriptif
- [ ] `php artisan geulis:ekspor` .xlsx sepuluh lembar, kode anonim

## Selesai bila

- [ ] Validator menyelesaikan lembar tanpa membuat akun
- [ ] V terhitung benar terhadap hitungan tangan
- [ ] Ekspor terbuka rapi di SPSS, tanpa satu pun nama siswa
- [ ] Observer bisa mengisi lembar dalam mode pesawat, terkirim saat daring

## Catatan berjalan

- 2026-09-10 — Sprint 0–4 selesai. Data contoh lokal: `php artisan db:seed --class=DemoSeeder` (guru `guru1`/`password`; siswa NIS `0056781234` PIN `482913`).
- Akun seed: username `admin`, surel `admin@geulis.test`, kata sandi `password` (ganti sebelum dipakai di sekolah).
