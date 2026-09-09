# GEULIS

**Geometri Etnomatematika untuk Literasi dan Inovasi Siswa** — LMS matematika berdiferensiasi berbasis etnomatematika Priangan Timur untuk mengembangkan *computational thinking* siswa SMA kelas XI pada materi transformasi geometri.

Produk Penelitian Kompetitif Universitas Siliwangi 2026 (skema PPKap, *Educational Design Research* model Plomp). Bukan produk komersial: akan divalidasi ahli, dilaporkan dalam artikel ilmiah, dan didaftarkan Hak Cipta.

## Mulai bekerja

Kontrak kerja untuk manusia maupun agen koding ada di **[`CLAUDE.md`](CLAUDE.md)** — baca seluruhnya sebelum menulis kode. Ringkasnya:

```bash
docker compose -f ~/code/docker-compose.yml up -d geulis-php geulis-nginx
docker exec geulis-php composer install
docker exec geulis-php php artisan migrate:fresh --seed
docker exec geulis-php php artisan test
docker exec -w /var/www/html/geulis laravel-node22 npm run build
```

Aplikasi: http://localhost:8020 · Panel admin: http://localhost:8020/admin (akun seed: `admin@geulis.test` / `password`, ganti sebelum dipakai).

Semua `php`, `composer`, dan `artisan` dijalankan **di dalam container** `geulis-php`, tidak pernah di host.

## Peta dokumen

| Berkas | Isi |
|---|---|
| `CLAUDE.md` | Kontrak kerja, aturan yang tidak bisa ditawar, ritual commit |
| `docs/SPRINT-SEKARANG.md` | Sprint yang sedang berjalan — dibaca setiap sesi |
| `docs/SPRINT-BRIEF.md` | Enam brief sprint |
| `docs/JURNAL-SESI.md` | Catatan tiap sesi kerja; bahan bagian metode artikel dan berkas HKI |
| `docs/spesifikasi/` | Cetak biru, wireframe, peta rute |
| `docs/CATATAN-DOCKER.md` | Hasil pencocokan lingkungan Docker |
| `docs/IDE-TAHUN-DEPAN.md` | Ide di luar cakupan tahun ini |

## Alur satu sesi (Claude Code)

`/mulai-sesi` → kerja pada satu sasaran → `/sesi-selesai` (Pint, uji, jurnal, commit). Hook `SessionEnd` menyimpan sisa perubahan sebagai commit `wip(sesi)` — jaring pengaman, bukan pengganti ritual.

## Lisensi

Hak cipta © 2026 tim peneliti GEULIS, Universitas Siliwangi. Kode inti (Mesin Diferensiasi, Motif Builder) adalah karya orisinal yang didaftarkan Hak Cipta; belum ada lisensi terbuka.
