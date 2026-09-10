# Sprint yang sedang berjalan

> Salin isi satu sprint dari `SPRINT-BRIEF.md` ke bawah garis ini saat sprint itu dimulai.
> Tandai `[x]` pada butir yang sudah selesai. Berkas ini dibaca agen koding setiap sesi.

**Sprint aktif:** — (Sprint 0–5 selesai 2026-09-10) · **Feature freeze** berlaku mulai awal Bulan 9
**Mulai:** —
**Target selesai:** —

---

## Sasaran

Tidak ada fitur baru. Hanya perbaikan kesalahan yang menghalangi pemakaian, persiapan validasi ahli, dan uji coba terbatas.

## Butir pekerjaan (persiapan pemakaian, bukan fitur)

- [ ] Ganti sandi akun `admin` dan `guru1`; hapus `DemoSeeder` dari basis data produksi (`migrate:fresh --seed` tanpa DemoSeeder)
- [ ] Isi varian konten P1–P5 lewat A-01 (±140 varian) — tim konten
- [ ] Unggah foto artefak lapangan (WebP ≤ 200 KB) menggantikan SVG contoh, lengkap dengan izin
- [ ] Uji di HP fisik lebar 360 px: S-01, S-03, S-05, S-07 Motif Builder, S-11, O-01
- [ ] Uji beban ±100 pengguna serentak (`k6`/`ab`) sebelum implementasi
- [ ] `GEULIS_PARAMETER_TERKUNCI=true` begitu validator pertama menerima tautan
- [ ] Cadangan harian basis data + `storage/app` (privat: produk siswa, ekspor)
- [ ] Pasang GeoGebra di server: `php artisan geulis:pasang-geogebra`

## Selesai bila

- [x] Sprint 0–5 tuntas: 190 uji hijau, `migrate:fresh --seed` bersih di MariaDB
- [ ] Validator pertama menyelesaikan lembar lewat tautan bertanda di server uji
- [ ] Sepuluh siswa uji terbatas menyelesaikan Pertemuan 1 dari HP masing-masing

## Catatan berjalan

- 2026-09-10 — Seluruh sprint selesai dalam satu hari kerja agen. Data contoh lokal: `php artisan db:seed --class=DemoSeeder` (guru `guru1`/`password`; siswa NIS `0056781234` PIN `482913`).
- Akun seed: username `admin`, surel `admin@geulis.test`, kata sandi `password` (ganti sebelum dipakai di sekolah).
- Peta rute: siswa `/belajar/*`, guru `/guru/*`, observer `/observasi`, peneliti `/riset/*`, validator `/validasi/{token}` (bertanda), admin `/admin` (Filament).
