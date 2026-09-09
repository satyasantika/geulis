---
description: Pemeriksaan mutu menyeluruh GEULIS — uji, gaya kode, migrasi dari nol, dan kepatuhan pada aturan yang tidak bisa ditawar.
disable-model-invocation: true
allowed-tools: [Bash, Read, Grep, Glob]
---

# Periksa mutu

Jalankan sebelum validasi ahli, sebelum uji coba di sekolah, atau kapan pun terasa ada yang meleset.

## 1. Uji dan gaya kode

```bash
docker exec geulis-php ./vendor/bin/pint --test
docker exec geulis-php php artisan test
```

## 2. Migrasi dari nol

```bash
docker exec geulis-php php artisan migrate:fresh --seed
```

Migrasi yang hanya jalan secara bertahap tetapi gagal dari nol akan meledak saat dipasang di server produksi menjelang implementasi — persis pada saat paling tidak ada waktu untuk memperbaikinya.

## 3. Kepatuhan pada aturan yang tidak bisa ditawar

Periksa satu per satu dan laporkan pelanggarannya, bukan hanya "aman":

**`adaptation_logs` hanya ditambah.** Cari `update`, `delete`, atau `truncate` pada tabel/model itu di luar berkas migrasi dan berkas uji. Setiap temuan adalah pelanggaran.

**Service tetap murni.** Cari pemanggilan Eloquent, fasad, `request()`, `auth()`, `config()`, atau `DB::` di dalam `app/Services/Differentiation/` dan `app/Services/Motif/`. Pengecualian yang sah hanya `DifferentiationConfig::fromConfig()`.

**Tidak ada kunci array bertipe float.** Cari pola `[0.` yang diikuti `=>` — PHP memaksa kunci float menjadi integer, dan seluruh ambang kategori runtuh diam-diam tanpa galat apa pun. Jebakan ini sudah pernah menggigit `AikenCalculator` dan `NGainCalculator`.

**Batas pengaman Mesin Diferensiasi masih diuji.** Pastikan kasus uji berikut masih ada dan hijau: tidak turun di bawah L1, tidak naik di atas L3, maksimal satu tingkat per pemeriksaan, `RULE_GUESS_GUARD` tidak mengubah M maupun level.

**Anonimisasi ekspor.** Pastikan tidak ada jalur kode yang bisa mengekspor nama atau NIS siswa ke berkas analisis.

**Data pribadi terlarang.** Cari kolom atau formulir yang meminta NIK, alamat rumah, nomor HP pribadi, atau foto wajah siswa.

**Atribusi aset budaya.** Pastikan `cultural_assets` yang dipakai di konten punya `perajin_sumber`, `lokasi`, `tanggal_dokumentasi`, dan `izin_diperoleh` terisi.

## 4. Laporan

Ringkas dalam satu tabel: aspek, status, temuan. Untuk setiap temuan, sebutkan berkas dan barisnya. **Jangan perbaiki apa pun tanpa persetujuan** — laporkan dulu, biar pengguna yang menentukan urutannya.
