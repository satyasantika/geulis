# Brief Sprint GEULIS

Enam sprint, urut. Satu sprint biasanya butuh beberapa sesi koding.

Cara pakai: salin isi satu sprint ke `SPRINT-SEKARANG.md` saat sprint itu dimulai. Agen koding membaca berkas itu setiap sesi lewat `/mulai-sesi`.

Produksi konten (±140 varian) berjalan **paralel** sejak Sprint 0 dan dikerjakan tim, bukan agen koding. Jangan menunggu kode selesai.

---

## Sprint 0 — Fondasi · Bulan 5–6

**Sasaran.** Guru bisa membuat kelas dan mencetak kartu PIN; siswa bisa masuk dan melihat beranda kosong.

**Dikerjakan**
1. Pasang Laravel di container, sambungkan ke MySQL, pastikan `php artisan migrate` jalan.
2. Salin enam berkas migrasi dari kerangka; jalankan `migrate:fresh` sampai bersih.
3. Model Eloquent untuk seluruh tabel — tipis: relasi, `$fillable`, `$casts`.
4. Autentikasi **NIS + PIN 6 digit**, bukan surel. Surel opsional untuk guru dan peneliti.
5. Peran dan otorisasi: `siswa`, `guru`, `validator`, `observer`, `peneliti`, `admin`. Middleware `role:`.
6. CRUD sekolah, kelas, dan pendaftaran siswa. Impor siswa dari CSV (nama, NIS, jenis kelamin).
7. Layar G-05: cetak kartu PIN satu kelas, siap gunting, ramah printer hitam-putih.
8. Layar S-02 persetujuan penelitian; siswa yang menolak tetap boleh belajar, datanya diberi tanda.
9. Tata letak dasar: Tailwind, mobile-first, satu tema.

**Selesai bila**
- `migrate:fresh --seed` jalan dari nol tanpa galat
- Guru bisa membuat kelas, mengimpor 30 siswa dari CSV, mencetak kartu PIN
- Siswa bisa masuk memakai NIS + PIN dari kartu itu di layar selebar 360 px
- Uji fitur untuk masuk, gagal masuk, dan otorisasi peran

**Jebakan.** Jangan pakai `Auth::routes()` bawaan yang berbasis surel. Alur masuk siswa berbeda dan ini menentukan seluruh pengalaman hari pertama di sekolah — kalau 34 siswa gagal masuk serentak, sesi pengambilan data hari itu hangus.

---

## Sprint 1 — Asesmen awal dan Mesin Diferensiasi · Bulan 6

**Sasaran.** Siswa selesai asesmen awal dan ditempatkan ke jalur belajarnya, dengan alasan penempatan yang tercatat.

**Dikerjakan**
1. Salin `DifferentiationEngine`, `DifferentiationConfig`, `AdaptationDecision` dari kerangka. **Jalankan ujinya lebih dahulu, sebelum menulis kode lain.** Semua harus hijau.
2. Tes Kesiapan Prasyarat: 15 butir, empat prasyarat, penskoran otomatis 0–100.
3. Angket Profil Belajar: 20 pernyataan, tiga modus, skor kecenderungan bukan label kaku.
4. Angket Minat Konteks Budaya: 6 pernyataan.
5. `PlacementService`: memanggil `tempatkan()`, menyimpan hasil beserta `penjelasan` ke `placements`.
6. `AdaptationRecorder`: menyimpan hasil `evaluasi()` ke `mastery_states` dan `adaptation_logs`. **Lapisan terpisah — jangan taruh Eloquent di dalam mesinnya.**
7. Layar S-03 asesmen awal, dengan penyimpanan sementara supaya siswa yang putus koneksi tidak mengulang dari awal.
8. Layar A-03: pengaturan parameter adaptasi, dengan peringatan besar bahwa layar ini dikunci setelah validasi ahli dimulai.

**Selesai bila**
- 16 kasus uji `DifferentiationEngineTest` hijau
- Dua siswa dengan skor kesiapan berbeda menghasilkan `level_awal` berbeda, dan alasannya terbaca di `placements.penjelasan`
- Setiap keputusan menulis satu baris ke `adaptation_logs`
- Ada uji fitur yang membuktikan `adaptation_logs` bertambah, tidak pernah diperbarui

**Jebakan.** Godaan terbesar di sini adalah menyederhanakan dengan memanggil model langsung dari dalam mesin. Jangan. Mesin harus bisa diuji tanpa basis data dan dijelaskan kepada validator ahli yang tidak membaca kode Laravel.

---

## Sprint 2 — Pertemuan, varian konten, GeoGebra · Bulan 6–7

**Sasaran.** Dua siswa berlevel berbeda membuka unit yang sama dan melihat varian konten yang berbeda.

**Dikerjakan**
1. `MeetingSeeder`: kerangka 5 pertemuan × 7 unit tetap.
2. Panel admin A-01: kelola varian konten dengan sumbu `level` × `modus`, termasuk nilai `*`.
3. `VariantResolver`: memilih varian paling spesifik yang cocok (logikanya sudah ada di `pilihVarian()`).
4. Layar S-05 halaman pertemuan dengan tujuh bagian tetap, kemajuan tersimpan per bagian.
5. **GeoGebra di-host sendiri**: unduh bundel Math Apps, taruh di `public/geogebra/`, muat `deployggb.js` dari sana. Jangan dari CDN.
6. Komponen Livewire pembungkus GeoGebra dengan konfigurasi per varian (materi awal, alat yang diaktifkan, mode terpandu).
7. CRUD aset budaya dengan validasi atribusi wajib di lapisan model.
8. Pengunci pertemuan: pertemuan berikutnya terbuka setelah yang sekarang selesai.

**Selesai bila**
- Satu unit punya empat varian dan siswa L1 serta L3 melihat yang berbeda
- GeoGebra termuat tanpa akses internet keluar
- Aset budaya tanpa `izin_diperoleh` ditolak saat disimpan
- Atribusi tampil di layar siswa

**Jebakan.** Jangan membuat sembilan varian per unit. Pakai `*` — tim tidak akan sanggup menulis 315 varian, dan jadwal Bulan 6–8 akan hangus.

---

## Sprint 3 — Motif Builder · Bulan 7

**Sasaran.** Siswa menyusun urutan perintah transformasi, menjalankannya, dan mendapat skor kemiripan otomatis.

**Dikerjakan**
1. Kanvas SVG dengan kisi koordinat, ramah sentuh, jalan pada lebar 360 px.
2. Blok perintah: `MOTIF_DASAR`, `TRANSLASI(a,b)`, `REFLEKSI(garis)`, `ROTASI(pusat,θ)`, `DILATASI(pusat,k)`, `ULANGI n KALI`. Susun dengan sentuh atau seret.
3. Mesin transformasi JavaScript: menjalankan urutan perintah, menggambar hasilnya.
4. Rasterisasi hasil ke kisi 200 × 200, kirim ke server.
5. Sambungkan ke `MotifScorer` — IoU, ambang lolos 90, efisiensi terpisah.
6. Alat Penanda Motif: siswa menandai motif dasar pada sasaran sebelum membangun. **Ini bukti dekomposisi** — tanpanya indikator itu tidak punya data.
7. Simpan `urutan_perintah`, skor, langkah, efisiensi, percobaan ke `motif_submissions`, plus cuplikan SVG.
8. Motif sasaran untuk kelima pertemuan, diturunkan dari aset budaya sungguhan.

**Selesai bila**
- Susunan yang benar menghasilkan kemiripan ≥ 90 dan ditandai lolos
- Susunan yang lebih panjang dari minimum tetap lolos, hanya efisiensinya lebih rendah
- Semua bisa dikerjakan dengan ibu jari di layar 360 px
- `buktiCT()` menghasilkan data untuk keempat indikator

**Jebakan.** Layar ini yang paling mudah gagal di HP kecil. Uji di perangkat sungguhan, bukan hanya di penyempit jendela peramban. Dan ingat: efisiensi rendah **tidak** menggugurkan kelulusan — algoritma benar yang bertele-tele tetap capaian yang sah.

---

## Sprint 4 — Tes CT, rubrik, analitik · Bulan 7–8

**Sasaran.** Guru membuka papan kelas dan langsung tahu siapa yang perlu didatangi hari ini.

**Dikerjakan**
1. Bank soal CT: empat indikator, stimulus berkonteks budaya, tipe pilihan ganda dan uraian.
2. Layar S-11 tes CT pra/pasca dengan pewaktu, penyimpanan sementara, dan tahan putus koneksi.
3. Penskoran otomatis untuk pilihan ganda; antrean penilaian manual untuk uraian dengan rubrik butir.
4. Rubrik analitik empat tingkat untuk produk siswa; rubrik ditampilkan ke siswa **sebelum** mengerjakan.
5. Layar S-10 unggah produk, empat bentuk pilihan, batas ukuran berkas dan kuota per siswa.
6. Layar S-09 Kemajuanku: skor per indikator CT, grafik penguasaan, galeri karya.
7. Layar G-01 papan kelas: peta panas penguasaan, daftar "perlu pendampingan", tombol override dengan alasan wajib.
8. Layar G-02 rapor siswa dan G-03 penilaian rubrik.

**Selesai bila**
- Guru melihat peta panas nyata dari data nyata
- Override tersimpan ke `teacher_overrides` lengkap dengan alasannya
- Siswa dengan `perlu_pendampingan` muncul di daftar guru
- Tes CT selesai utuh meski koneksi putus di tengah

**Jebakan.** Papan kelas mudah berubah jadi tabel penuh angka yang tidak menjawab apa pun. Rancang untuk satu pertanyaan: siapa yang perlu saya datangi hari ini.

---

## Sprint 5 — Modul penelitian · Bulan 8

**Sasaran.** Validator mengisi lembar lewat tautan, rekap Aiken's V terbit otomatis, data siap diekspor.

**Dikerjakan**
1. Instrumen validasi: empat aspek, ±28 butir, skala 1–5, kolom saran per butir.
2. Tautan bertanda untuk validator — **tanpa perlu membuat akun**. Hambatan sekecil ini menentukan apakah ahli benar-benar mengisi.
3. Layar V-01 lembar validasi, bisa disimpan sebagian dan dilanjutkan.
4. Sambungkan `AikenCalculator`; layar V-02 rekap V per butir, aspek, dan keseluruhan.
5. Ekspor rekap saran perbaikan sebagai daftar tugas revisi.
6. Angket respons siswa dan guru, muncul otomatis setelah pertemuan terakhir.
7. Layar O-01 lembar observasi ramah HP, **jalan tanpa sinyal** dan mengirim ulang saat sinyal kembali.
8. Layar P-01 papan kelengkapan data per kelas.
9. Layar P-02 analitik: N-Gain per siswa, kelas, indikator, kelompok, plus statistik deskriptif.
10. Perintah `php artisan geulis:ekspor` menghasilkan .xlsx sepuluh lembar, **selalu dengan kode anonim**.

**Selesai bila**
- Validator menyelesaikan lembar tanpa membuat akun
- V terhitung benar terhadap hitungan tangan
- Ekspor terbuka rapi di SPSS dan tidak memuat satu pun nama siswa
- Observer bisa mengisi lembar dalam mode pesawat, terkirim saat daring

**Jebakan.** Uji-t **tidak** dihitung di dalam sistem. Sistem hanya menyiapkan data; statistik inferensial dijalankan di SPSS/JASP supaya dapat diaudit reviewer.

---

## Setelah Sprint 5 — Feature freeze, awal Bulan 9

Tidak ada fitur baru sampai implementasi di sekolah selesai. Hanya perbaikan kesalahan yang menghalangi pemakaian.

Alasannya metodologis, bukan administratif: begitu validator mulai menilai, setiap perubahan sistem membuat hasil validasi tidak lagi berlaku bagi produk yang diuji. Itu kelemahan yang paling mudah ditemukan reviewer dan paling mahal untuk diperbaiki.

Ide bagus yang muncul setelah titik ini dicatat ke `docs/IDE-TAHUN-DEPAN.md`.
