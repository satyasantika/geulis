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
- [ ] Model Eloquent seluruh tabel — tipis
- [ ] Autentikasi NIS + PIN 6 digit
- [ ] Peran dan middleware `role:`
- [ ] CRUD sekolah, kelas, pendaftaran siswa; impor CSV
- [ ] Layar G-05 cetak kartu PIN
- [ ] Layar S-02 persetujuan penelitian
- [ ] Tata letak dasar Tailwind, mobile-first

## Selesai bila

- [x] `migrate:fresh --seed` jalan dari nol tanpa galat
- [ ] Guru bisa membuat kelas, impor 30 siswa dari CSV, cetak kartu PIN
- [ ] Siswa bisa masuk memakai NIS + PIN pada layar selebar 360 px
- [ ] Uji fitur untuk masuk, gagal masuk, dan otorisasi peran

## Catatan berjalan

- 2026-09-10 — Kerangka dan bahan vibecoding terpasang; 33 uji hijau (16 Mesin Diferensiasi + 11 penghitung riset + Filament). Rincian di `docs/JURNAL-SESI.md`.
- Model yang sudah ada: `User`, `School`, `Role`, `Classroom`, `Meeting`, `LessonUnit`, `ContentVariant`, `MasteryState`, `AdaptationLog`. Butir 3 (model seluruh tabel) masih menyisakan ±30 tabel.
- Filament 5 dipakai untuk panel admin A-xx. `User::canAccessPanel()` masih mengembalikan `true` untuk semua pengguna — **harus dibatasi ke peran admin/peneliti** saat butir 5 (peran + middleware `role:`) dikerjakan.
- Akun seed: username `admin`, surel `admin@geulis.test`, kata sandi `password` (ganti sebelum dipakai di sekolah).
