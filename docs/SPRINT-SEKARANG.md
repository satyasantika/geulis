# Sprint yang sedang berjalan

> Salin isi satu sprint dari `SPRINT-BRIEF.md` ke bawah garis ini saat sprint itu dimulai.
> Tandai `[x]` pada butir yang sudah selesai. Berkas ini dibaca agen koding setiap sesi.

**Sprint aktif:** 1 — Asesmen awal dan Mesin Diferensiasi
**Mulai:** 2026-09-10
**Target selesai:** akhir Bulan 6

---

## Sasaran

Siswa selesai asesmen awal dan ditempatkan ke jalur belajarnya, dengan alasan penempatan yang tercatat.

## Butir pekerjaan

- [x] `DifferentiationEngine`, `DifferentiationConfig`, `AdaptationDecision` tersalin; 16 uji hijau
- [ ] Tes Kesiapan Prasyarat: 15 butir, empat prasyarat, penskoran otomatis 0–100
- [ ] Angket Profil Belajar: 20 pernyataan, tiga modus, skor kecenderungan
- [ ] Angket Minat Konteks Budaya: 6 pernyataan
- [ ] `PlacementService`: memanggil `tempatkan()`, menyimpan hasil + `penjelasan` ke `placements`
- [ ] `AdaptationRecorder`: menyimpan hasil `evaluasi()` ke `mastery_states` dan `adaptation_logs`
- [ ] Layar S-03 asesmen awal dengan penyimpanan sementara
- [ ] Layar A-03 pengaturan parameter adaptasi + peringatan kunci

## Selesai bila

- [x] 16 kasus uji `DifferentiationEngineTest` hijau
- [ ] Dua siswa dengan skor kesiapan berbeda menghasilkan `level_awal` berbeda, alasannya terbaca di `placements.penjelasan`
- [ ] Setiap keputusan menulis satu baris ke `adaptation_logs`
- [ ] Uji fitur membuktikan `adaptation_logs` bertambah, tidak pernah diperbarui

## Catatan berjalan

- 2026-09-10 — Sprint 0 selesai (commit `feat(guru): kelas, impor csv siswa, kartu pin, dan persetujuan penelitian`). Guru: `/guru`; siswa: `/masuk` → `/belajar/persetujuan` → `/belajar`.
- Akun seed: username `admin`, surel `admin@geulis.test`, kata sandi `password` (ganti sebelum dipakai di sekolah).
