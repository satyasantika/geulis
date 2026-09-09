# Catatan Docker — yang perlu dicocokkan

Container dan MySQL sudah Anda siapkan, jadi berkas di sini **bukan pengganti** melainkan daftar cocok. Periksa tujuh hal ini sebelum Sprint 0 dimulai; masing-masing pernah menjadi sebab kegagalan yang memakan waktu berjam-jam untuk dilacak.

## Daftar cocok

**1. Nama layanan.** Seluruh dokumen dan skill memakai `app` (PHP) dan `db` (MySQL). Kalau nama Anda berbeda, ganti **sekali** di `CLAUDE.md` bagian 2 dan di `.cursor/rules/00-proyek.mdc`, lalu jangan diubah lagi. Sesi koding berikutnya membaca dari sana.

**2. `DB_HOST` adalah nama layanan, bukan `localhost`.** Dari dalam container aplikasi, MySQL dijangkau lewat nama layanannya di jaringan Docker. `DB_HOST=localhost` akan gagal dengan pesan yang menyesatkan.

**3. Ekstensi PHP.** Laravel perlu `pdo_mysql`, `mbstring`, `bcmath`, `gd`, `zip`, `intl`, `xml`, `curl`. `gd` dan `intl` sering luput pada image ramping. Periksa:
```bash
docker exec geulis-php php -m
```

**4. Composer dan Node ada di dalam container.** Kalau belum, tambahkan — bekerja bergantian antara host dan container adalah sumber galat yang paling melelahkan.
```bash
docker exec geulis-php composer --version
docker exec geulis-php node --version
```

**5. Izin tulis pada `storage/` dan `bootstrap/cache/`.** Ini penyebab layar putih paling umum pada Laravel di Docker, terutama saat volume di-mount dari Windows.
```bash
docker exec geulis-php chmod -R ug+w storage bootstrap/cache
```

**6. Zona waktu.** Setel `Asia/Jakarta` di aplikasi (`APP_TIMEZONE`) **dan** di MySQL. Data penelitian ini berisi cap waktu pengerjaan siswa yang dipakai `RULE_GUESS_GUARD` untuk membandingkan durasi terhadap median kelas — selisih tujuh jam akan merusaknya diam-diam.

**7. Volume data MySQL bertahan.** Pastikan ada volume bernama, bukan penyimpanan sementara. Kehilangan basis data saat container dibangun ulang bukan hal yang bisa diperbaiki menjelang pengambilan data.

## Rujukan minimum

Kalau perlu membandingkan, bentuk paling sederhana yang memenuhi tujuh butir di atas:

```yaml
services:
  app:
    build: ./docker/php
    volumes:
      - .:/var/www/html
    ports:
      - "8000:8000"
    depends_on:
      db:
        condition: service_healthy
    environment:
      TZ: Asia/Jakarta

  db:
    image: mysql:8
    environment:
      MYSQL_DATABASE: geulis
      MYSQL_USER: geulis
      MYSQL_PASSWORD: rahasia
      MYSQL_ROOT_PASSWORD: rahasia
      TZ: Asia/Jakarta
    volumes:
      - data-mysql:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 5s
      retries: 10

volumes:
  data-mysql:
```

`healthcheck` pada `db` dan `condition: service_healthy` pada `app` mencegah masalah klasik: aplikasi menyala lebih cepat daripada MySQL siap menerima koneksi, lalu migrasi gagal dengan galat koneksi yang membingungkan.

## Menjelang implementasi di sekolah (Bulan 10)

Sebelum tiga sekolah memakai sistem serentak, jalankan uji beban sederhana dari host:

```bash
ab -n 500 -c 50 http://localhost:8000/
```

Beban puncak realistis hanya sekitar 105 pengguna dan biasanya tidak serentak karena jadwal pelajaran berbeda. Tapi mengetahuinya dua minggu sebelum hari-H jauh lebih baik daripada mengetahuinya di depan kelas.

---

## Hasil pencocokan — 2026-09-10

Dicocokkan terhadap `~/code/docker-compose.yml` (proyek compose `code`, dipakai bersama beberapa aplikasi). Tidak ada berkas compose di repo ini.

| # | Butir | Keadaan | Tindak lanjut |
|---|---|---|---|
| 1 | Nama layanan | PHP = `geulis-php`, web = `geulis-nginx` (port 8020). **Tidak ada layanan `db`.** Semua dokumen dan skill sudah memakai `docker exec geulis-php`. | Selesai |
| 2 | `DB_HOST` | MariaDB 10.11 berjalan di host WSL; container menjangkaunya lewat `host.docker.internal` (`extra_hosts: host-gateway`). | Selesai |
| 3 | Ekstensi PHP | PHP 8.3.33: pdo_mysql, mbstring, bcmath, gd, zip, intl, xml, curl semua ada. | Selesai |
| 4 | Composer & Node | Composer 2.9 ada di `geulis-php`. **Node tidak ada di `geulis-php`**; pakai container `laravel-node22` (Node 22, repo ter-mount di `/var/www/html/geulis`). Node di host hanya v18 — jangan dipakai untuk Vite 8. | Selesai |
| 5 | Izin tulis | `storage/` dan `bootstrap/cache/` dapat ditulis `www-data` dari container. | Selesai |
| 6 | Zona waktu | MariaDB host = SYSTEM (WIB). `APP_TIMEZONE=Asia/Jakarta` di `.env`. Container `geulis-php` sendiri masih UTC (`TZ` kosong) — tidak berdampak pada Laravel karena `APP_TIMEZONE` menang, tetapi cap waktu di log PHP/FPM akan UTC. | **Disarankan** menambah `TZ: Asia/Jakarta` pada `environment` `geulis-php` di `~/code/docker-compose.yml` (di luar repo). |
| 7 | Volume MySQL | Data MariaDB tinggal di host, bukan di container — tidak hilang saat container dibangun ulang. Cadangkan lewat `mysqldump` dari host secara berkala menjelang pengambilan data. | Selesai |

Ekstra: `docker compose` harus dijalankan dengan `-f ~/code/docker-compose.yml` (atau dari `~/code`), karena repo ini bukan akar proyek compose.
