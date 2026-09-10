# Sprint yang sedang berjalan

> Salin isi satu sprint dari `SPRINT-BRIEF.md` ke bawah garis ini saat sprint itu dimulai.
> Tandai `[x]` pada butir yang sudah selesai. Berkas ini dibaca agen koding setiap sesi.

**Sprint aktif:** 2 — Pertemuan, varian konten, GeoGebra
**Mulai:** 2026-09-10
**Target selesai:** Bulan 6–7

---

## Sasaran

Dua siswa berlevel berbeda membuka unit yang sama dan melihat varian konten yang berbeda.

## Butir pekerjaan

- [x] `MeetingSeeder`: kerangka 5 pertemuan × 7 unit
- [ ] Panel admin A-01: kelola varian konten `level` × `modus`, termasuk `*`
- [ ] `VariantResolver`: memilih varian paling spesifik (`pilihVarian()`)
- [ ] Layar S-05 halaman pertemuan, tujuh bagian, kemajuan per bagian
- [ ] GeoGebra swadaya di `public/geogebra/`, tanpa CDN
- [ ] Komponen Livewire pembungkus GeoGebra per varian
- [ ] CRUD aset budaya dengan atribusi wajib di lapisan model
- [ ] Pengunci pertemuan

## Selesai bila

- [ ] Satu unit punya empat varian dan siswa L1 serta L3 melihat yang berbeda
- [ ] GeoGebra termuat tanpa akses internet keluar
- [ ] Aset budaya tanpa `izin_diperoleh` ditolak saat disimpan
- [ ] Atribusi tampil di layar siswa

## Catatan berjalan

- 2026-09-10 — Sprint 0 dan 1 selesai. Alur siswa: `/masuk` → `/belajar/persetujuan` → `/belajar/asesmen-awal` → `/belajar`.
- Akun seed: username `admin`, surel `admin@geulis.test`, kata sandi `password` (ganti sebelum dipakai di sekolah).
