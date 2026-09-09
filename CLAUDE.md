# GEULIS — Panduan Kerja untuk Agen Koding

Berkas ini dibaca setiap sesi. Perlakukan sebagai kontrak kerja, bukan saran.

---

## 1. Apa yang sedang dibangun

**GEULIS** — *Geometri Etnomatematika untuk Literasi dan Inovasi Siswa*: LMS pembelajaran matematika berdiferensiasi berbasis etnomatematika Priangan Timur untuk mengembangkan *computational thinking* (CT) siswa SMA kelas XI pada materi transformasi geometri.

Ini **produk penelitian**, bukan produk komersial. Penelitian Kompetitif Universitas Siliwangi 2026, skema PPKap, metode *Educational Design Research* model Plomp. Konsekuensinya melekat di setiap keputusan teknis:

- Sistem akan **divalidasi ahli** — jadi logikanya harus bisa dijelaskan, bukan kotak hitam.
- Sistem akan **dilaporkan dalam artikel ilmiah** — jadi setiap keputusan otomatis harus meninggalkan jejak data.
- Sistem akan **didaftarkan Hak Cipta** — jadi kode inti harus orisinal dan riwayat Git harus rapi sejak baris pertama.
- Subjeknya **anak di bawah umur** — jadi privasi bukan fitur tambahan.

Spesifikasi lengkap: `docs/spesifikasi/CETAK-BIRU-LMS-CT.md`. Kalau ada pertanyaan desain yang tidak terjawab di sini, jawabannya ada di sana. Kalau di sana pun tidak ada, **tanya, jangan mengarang**.

---

## 2. Lingkungan pengembangan

Berjalan di Docker. Berkas compose **tidak ada di repo ini**, melainkan di `~/code/docker-compose.yml` (proyek `code`, dipakai bersama beberapa aplikasi). Layanan yang relevan:

| Layanan / container | Peran |
|---|---|
| `geulis-php` | PHP 8.3 FPM + Composer. Repo ini ter-mount di `/var/www/html`. **Tidak ada Node.** |
| `geulis-nginx` | Web server → http://localhost:8020 |
| `laravel-node22` | Node 22 untuk Vite; repo ter-mount di `/var/www/html/geulis` |
| *(host)* MariaDB 10.11 | Dijangkau dari container lewat `DB_HOST=host.docker.internal`, basis `db_geulis`, zona waktu WIB |

```bash
docker compose -f ~/code/docker-compose.yml up -d geulis-php geulis-nginx   # nyalakan
docker exec geulis-php php artisan migrate                                   # migrasi
docker exec geulis-php php artisan test                                      # seluruh uji
docker exec geulis-php php artisan test --filter=DifferentiationEngine
docker exec geulis-php ./vendor/bin/pint                                     # gaya kode
docker exec geulis-php composer install
docker exec -w /var/www/html/geulis laravel-node22 npm run build             # Vite (aset)
docker exec geulis-php php artisan db:seed --class=MeetingSeeder
```

Aturan yang tidak boleh dilanggar: **jangan pernah menjalankan `php`, `composer`, atau `artisan` di host.** Semua perintah lewat `docker exec geulis-php`. Versi PHP di host dan di container berbeda, dan galat yang muncul dari perbedaan itu memakan waktu berjam-jam untuk dilacak. Satu-satunya pengecualian: MCP Laravel Boost di `.mcp.json` dijalankan Claude Code dari host, tetapi perintahnya sendiri sudah dibungkus `docker exec -i geulis-php`.

`.env` tidak pernah masuk Git. Kalau menambah variabel baru, tambahkan juga ke `.env.example` pada commit yang sama.

---

## 3. Tumpukan teknologi

| Lapis | Pilihan | Catatan |
|---|---|---|
| Kerangka | Laravel 13 | PHP 8.3 |
| Antarmuka siswa & guru | Livewire + Alpine.js + Tailwind 4 | Tanpa SPA, tanpa API terpisah |
| Panel admin & peneliti | Filament 5 | **Hanya** untuk layar A-xx (dan P-xx bila cocok). Alasan: sudah terpasang sejak awal, berbasis Livewire, lisensi MIT, bukan bagian klaim orisinalitas HKI. Layar siswa/guru **tidak** memakai Filament. |
| Basis data | MySQL 8 / MariaDB 10.11 | Kolom JSON dipakai untuk konfigurasi aturan |
| Uji | Pest | Wajib, bukan opsional. Uji baru ditulis gaya Pest; kelas PHPUnit lama tetap dijalankan Pest. |
| Aset | Vite | |
| Geometri | GeoGebra Apps API, **di-host sendiri** | Jangan tarik dari CDN — sekolah memblokirnya |
| Motif Builder | SVG + mesin transformasi buatan sendiri | Komponen orisinal, inti nilai HKI |

**Jangan menambah dependensi tanpa alasan yang ditulis.** Setiap paket baru harus lolos tiga pertanyaan: apakah benar-benar perlu, apakah lisensinya permisif (MIT/BSD/Apache), dan apakah menambah risiko pada klaim orisinalitas HKI. Kalau ragu, tanya.

---

## 4. Peta kode

```
app/
├── Models/                        Eloquent — tipis, hanya relasi & cast
├── Services/
│   ├── Differentiation/           MESIN DIFERENSIASI — jantung sistem
│   ├── Motif/                     Penskoran Motif Builder (IoU)
│   └── Research/                  Aiken's V, N-Gain
├── Livewire/                      Komponen antarmuka siswa & guru
├── Filament/                      Panel admin (A-xx) — resource, bukan logika bisnis
└── Http/Controllers/              Tipis; logika ada di Services

database/migrations/               Skema; jangan sunting migrasi yang sudah dipakai
database/seeders/                  Kerangka konten saja, bukan isi konten
tests/Unit/                        Logika murni — Mesin Diferensiasi, penghitung
tests/Feature/                     Alur pengguna
docs/spesifikasi/                  Cetak biru & wireframe
docs/JURNAL-SESI.md                Catatan tiap sesi kerja
docs/SPRINT-SEKARANG.md            Sprint yang sedang berjalan — BACA SETIAP SESI
```

**Kelas di `app/Services/` adalah logika murni.** Tanpa Eloquent, tanpa fasad, tanpa `request()`, tanpa `auth()`. Masukan lewat argumen, keluaran lewat nilai balik. Alasannya bukan kerapian melainkan kebutuhan: kelas-kelas ini harus bisa diuji tanpa basis data dan harus bisa dijelaskan kepada validator ahli yang tidak membaca kode Laravel.

Penyimpanan hasilnya dikerjakan lapisan terpisah (`AdaptationRecorder` dan sejenisnya).

---

## 5. Bahasa dan penamaan

Proyek ini dibaca dosen, mahasiswa, dan pemeriksa HKI Indonesia. Aturannya campuran, dan campurannya disengaja:

- **Istilah domain dalam Bahasa Indonesia**: `nilai_m`, `level_kini`, `modus_utama`, `iterasi_remedial`, `perlu_pendampingan`, `urutan_perintah`, `skor_kemiripan`, `alasan`.
- **Konvensi kerangka dalam Bahasa Inggris**: nama kelas Laravel (`MasteryState`, `LessonUnit`), nama metode HTTP, nama tabel jamak Inggris (`adaptation_logs`, `content_variants`).
- **Komentar dan dokumentasi dalam Bahasa Indonesia**, dan jelaskan **mengapa**, bukan **apa**. Kode sudah mengatakan apa.
- **Teks antarmuka dalam Bahasa Indonesia**, dan ini penting: sistem tidak pernah menyebut siswa "lemah", "bodoh", atau "gagal". Yang disebut adalah posisi sementara pada satu unit materi, selalu disertai jalan keluarnya. Baca ulang `pesanSiswa` pada `AdaptationDecision` untuk melihat nadanya.

Gaya kode: PSR-12. Jalankan Pint sebelum commit.

---

## 6. Aturan yang tidak bisa ditawar

Sembilan hal berikut bukan preferensi. Melanggarnya merusak penelitian, bukan sekadar merusak kode.

1. **Mesin Diferensiasi tidak boleh disentuh tanpa uji.** Setiap perubahan aturan wajib disertai kasus uji di `tests/Unit/DifferentiationEngineTest.php`. Kesalahan di sini tidak terlihat di layar dan baru ketahuan setelah data satu semester terlanjur terkumpul.

2. **`adaptation_logs` hanya ditambah, tidak pernah diperbarui atau dihapus.** Tabel itu bukti empiris bahwa sistemnya adaptif dan bahan mentah bagian temuan artikel. Tidak ada `update()`, tidak ada `delete()`, tidak ada `truncate()` pada tabel itu di kode produksi.

3. **`config/geulis.php` dibekukan begitu validasi ahli dimulai.** Parameternya (α, ambang, batas remedial) ikut dinilai pada lembar validasi. Mengubahnya setelah itu membatalkan keabsahan hasil validasi terhadap produk yang diuji. Kalau ada perintah mengubahnya, konfirmasi dulu apakah validasi sudah berjalan.

4. **Ekspor data selalu memakai kode anonim** (`S-001`, `S-002`, …). Tabel pemetaan ke identitas asli hanya untuk ketua peneliti. `anonimkan_ekspor` tidak boleh diberi jalan pintas untuk dimatikan.

5. **Jangan pernah mengumpulkan** NIK, alamat rumah, nomor HP pribadi siswa, atau foto wajah. Kalau sebuah fitur seolah memerlukannya, fiturnya yang salah.

6. **Setiap aset budaya wajib punya** `perajin_sumber`, `lokasi`, `tanggal_dokumentasi`, dan `izin_diperoleh`. Validasi ini ditegakkan di lapisan model, bukan hanya di formulir. Atribusinya ditampilkan di layar siswa.

7. **Mobile-first, tanpa kecuali.** Sebagian besar siswa membuka sistem ini dari HP, bukan lab komputer. Setiap layar — termasuk Motif Builder — wajib dapat digunakan pada lebar 360 px.

8. **Jangan menambah fitur di luar cakupan.** Yang di luar cakupan tahun ini: tutor AI/LLM, kelas daring sinkron, materi di luar transformasi geometri, gamifikasi dengan papan peringkat, aplikasi native, integrasi Dapodik/SSO. Kalau sebuah ide terasa bagus tapi ada di daftar ini, catat di `docs/IDE-TAHUN-DEPAN.md` dan lanjutkan.

9. **Setelah *feature freeze* awal Bulan 9**, tidak ada fitur baru sampai implementasi selesai — hanya perbaikan kesalahan yang menghalangi pemakaian.

---

## 7. Selesai artinya apa

Sebuah perubahan dianggap selesai hanya bila **semuanya** terpenuhi:

- [ ] Uji yang relevan ditulis dan **hijau**: `docker exec geulis-php php artisan test`
- [ ] Pint bersih: `docker exec geulis-php ./vendor/bin/pint`
- [ ] Migrasi jalan dari nol: `php artisan migrate:fresh --seed` tidak galat
- [ ] Layar baru sudah dicoba pada lebar 360 px
- [ ] Variabel `.env` baru sudah masuk `.env.example`
- [ ] Perubahan pada aturan adaptasi sudah tercatat di `docs/JURNAL-SESI.md` beserta alasannya
- [ ] Sudah di-commit (lihat bagian 8)

Jangan laporkan pekerjaan selesai bila ada uji yang merah. Kalau buntu, katakan buntu — itu jauh lebih berguna daripada laporan selesai yang keliru.

---

## 8. Ritual sesi — commit tiap sesi, tanpa kecuali

Riwayat Git yang rapi sejak baris pertama adalah **bukti penciptaan terkuat dan termurah** untuk pendaftaran Hak Cipta. Ia juga satu-satunya cara sesi berikutnya tahu apa yang sudah dikerjakan.

**Awal sesi** — jalankan `/mulai-sesi`, atau kerjakan manual:
1. Baca `docs/SPRINT-SEKARANG.md`
2. Baca tiga entri terakhir `docs/JURNAL-SESI.md`
3. `git log --oneline -10` dan `git status`
4. Sebutkan sasaran sesi ini dalam satu kalimat sebelum menulis kode

**Akhir sesi** — jalankan `/sesi-selesai`. Skill itu menjalankan uji, Pint, menulis entri jurnal, lalu commit.

Ada jaring pengaman: hook `SessionEnd` otomatis meng-commit apa pun yang tersisa sebagai `wip(sesi): ...`. **Jaring pengaman bukan pengganti disiplin** — commit `wip` yang menumpuk membuat riwayat sulit dibaca, dan riwayat yang sulit dibaca melemahkan berkas HKI.

### Format pesan commit

```
<tipe>(<lingkup>): <ringkasan dalam Bahasa Indonesia, huruf kecil, tanpa titik>

<badan opsional: mengapa, bukan apa>

Sprint: <nomor>
```

Tipe: `feat` `fix` `test` `refactor` `docs` `chore` `wip`
Lingkup: `mesin` `motif` `riset` `konten` `auth` `guru` `siswa` `db` `docker` `ui`

Contoh:

```
feat(mesin): tambahkan RULE_GUESS_GUARD pada evaluasi penguasaan

Siswa yang mengerjakan pemeriksaan jauh lebih cepat dari median kelas
dengan skor rendah tidak lagi diturunkan levelnya. Penurunan level akibat
tebak-tebakan mencemari data adaptasi dan tidak adil bagi siswa.

Sprint: 1
```

**Satu commit, satu maksud.** Jangan campur perbaikan kesalahan dengan fitur baru.

---

## 9. Kalau ragu

- Pertanyaan desain → `docs/spesifikasi/CETAK-BIRU-LMS-CT.md`
- Pertanyaan tampilan → `docs/spesifikasi/wireframe-lms-geulis.html`
- Sprint mana yang berjalan → `docs/SPRINT-SEKARANG.md`
- Apa yang dikerjakan sesi lalu → `docs/JURNAL-SESI.md`
- Masih tidak terjawab → **tanya**. Menebak spesifikasi penelitian jauh lebih mahal daripada bertanya.

===

---

## 10. Panduan Laravel Boost (dibuat otomatis)

Blok di bawah ini ditulis dan diperbarui oleh `php artisan boost:install` / `boost:update`; jangan disunting tangan. Dua catatan supaya tidak bertentangan dengan bagian 2:

- Setiap `php artisan …`, `composer …`, atau `vendor/bin/pint` di blok itu dibaca sebagai `docker exec geulis-php …`.
- Bila panduan Boost dan bagian 1–9 berbeda, **bagian 1–9 yang menang** — itu kontrak penelitian, Boost hanya konvensi kerangka.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Test every code change by adding or updating a test.
- Run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
