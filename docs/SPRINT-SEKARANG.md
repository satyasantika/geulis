# Sprint yang sedang berjalan

> Salin isi satu sprint dari `SPRINT-BRIEF.md` ke bawah garis ini saat sprint itu dimulai.
> Tandai `[x]` pada butir yang sudah selesai. Berkas ini dibaca agen koding setiap sesi.

**Sprint aktif:** 4 — Tes CT, rubrik, analitik
**Mulai:** 2026-09-10
**Target selesai:** Bulan 7–8

---

## Sasaran

Guru membuka papan kelas dan langsung tahu siapa yang perlu didatangi hari ini.

## Butir pekerjaan

- [ ] Bank soal CT: empat indikator, stimulus berkonteks budaya, PG dan uraian
- [ ] Layar S-11 tes CT pra/pasca: pewaktu, penyimpanan sementara, tahan putus koneksi
- [ ] Penskoran otomatis PG; antrean penilaian manual uraian dengan rubrik butir
- [ ] Rubrik analitik empat tingkat untuk produk; ditampilkan ke siswa sebelum mengerjakan
- [ ] Layar S-10 unggah produk: empat bentuk, batas ukuran & kuota
- [ ] Layar S-09 Kemajuanku: skor per indikator CT, grafik penguasaan, galeri karya
- [ ] Layar G-01 papan kelas: peta panas, daftar "perlu pendampingan", override + alasan wajib
- [ ] Layar G-02 rapor siswa dan G-03 penilaian rubrik

## Selesai bila

- [ ] Guru melihat peta panas nyata dari data nyata
- [ ] Override tersimpan ke `teacher_overrides` lengkap dengan alasannya
- [ ] Siswa dengan `perlu_pendampingan` muncul di daftar guru
- [ ] Tes CT selesai utuh meski koneksi putus di tengah

## Catatan berjalan

- 2026-09-10 — Sprint 0–3 selesai. Data contoh lokal: `php artisan db:seed --class=DemoSeeder` (guru `guru1`/`password`; siswa NIS `0056781234` PIN `482913`).
- Akun seed: username `admin`, surel `admin@geulis.test`, kata sandi `password` (ganti sebelum dipakai di sekolah).
