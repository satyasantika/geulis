# Sprint yang sedang berjalan

> Salin isi satu sprint dari `SPRINT-BRIEF.md` ke bawah garis ini saat sprint itu dimulai.
> Tandai `[x]` pada butir yang sudah selesai. Berkas ini dibaca agen koding setiap sesi.

**Sprint aktif:** 0 — Fondasi
**Mulai:** 2026-09-10
**Target selesai:** akhir Bulan 6

---

## Sasaran

Guru bisa membuat kelas dan mencetak kartu PIN; siswa bisa masuk dan melihat beranda kosong.

## Butir pekerjaan

- [x] Laravel terpasang di container, tersambung MySQL, `php artisan migrate` jalan
- [x] Enam berkas migrasi disalin dari kerangka; `migrate:fresh` bersih
- [ ] Model Eloquent seluruh tabel — tipis (dibuat saat tabelnya dipakai; sesi ini: `Role` dilengkapi)
- [x] Autentikasi NIS + PIN 6 digit
- [x] Peran dan middleware `role:`
- [ ] CRUD sekolah, kelas, pendaftaran siswa; impor CSV
- [ ] Layar G-05 cetak kartu PIN
- [ ] Layar S-02 persetujuan penelitian
- [x] Tata letak dasar Tailwind, mobile-first (`components.layouts.app`; pemeriksaan visual di HP masih terbuka)

## Selesai bila

- [x] `migrate:fresh --seed` jalan dari nol tanpa galat
- [ ] Guru bisa membuat kelas, impor 30 siswa dari CSV, cetak kartu PIN
- [x] Siswa bisa masuk memakai NIS + PIN pada layar selebar 360 px
- [x] Uji fitur untuk masuk, gagal masuk, dan otorisasi peran

## Catatan berjalan

- 2026-09-10 — Kerangka dan bahan vibecoding terpasang; 33 uji hijau (16 Mesin Diferensiasi + 11 penghitung riset + Filament). Rincian di `docs/JURNAL-SESI.md`.
- Model yang sudah ada: `User`, `School`, `Role`, `Classroom`, `Meeting`, `LessonUnit`, `ContentVariant`, `MasteryState`, `AdaptationLog`. Butir 3 (model seluruh tabel) masih menyisakan ±30 tabel.
- Filament 5 dipakai untuk panel admin A-xx. `User::canAccessPanel()` sudah dibatasi ke admin/peneliti yang aktif (2026-09-10).
- 2026-09-10 — Autentikasi, enam peran, middleware `role:`, tata letak dasar selesai; 58 uji hijau. Siswa/guru masuk lewat `/masuk` (S-01), pengelola lewat `/admin/login`.
- Akun seed: username `admin`, surel `admin@geulis.test`, kata sandi `password` (ganti sebelum dipakai di sekolah).
