# Jurnal Sesi Koding GEULIS

Entri terbaru di atas. Ditulis oleh `/sesi-selesai` pada akhir tiap sesi.

Berkas ini punya tiga pembaca, dan ketiganya penting:

1. **Sesi koding berikutnya** — supaya tidak mulai dari nol.
2. **Penulis artikel** — bagian "Keputusan desain" adalah bahan mentah bagian metode. Alasan di balik setiap parameter jauh lebih sulit direkonstruksi enam bulan kemudian daripada ditulis lima menit sekarang.
3. **Berkas HKI** — jurnal ini bersama riwayat Git adalah bukti proses penciptaan.

---

## Format

```markdown
## YYYY-MM-DD · Sprint N · <sasaran sesi dalam satu frasa>

**Selesai**
- ...

**Tidak selesai**
- ... — sebabnya ...

**Keputusan desain**
- ... karena ...

**Sesi berikutnya**
- ...

**Commit:** `<hash pendek>` `<pesan>`
```

---

## 2026-09-10 · Sprint 1 · asesmen awal, penempatan, dan perekam adaptasi — Sprint 1 selesai

**Selesai**
- Model `ReadinessItem`, `ReadinessResponse`, `LearningProfile`, `Placement`; `ReadinessItemSeeder` 15 butir (koordinat 4, bangun datar 4, simetri 4, bilangan bulat 3); butir angket profil (20) dan minat (6) di `config/angket.php`.
- `PenskorKesiapan`, `PenskorAngket` (murni). `PlacementService` memanggil `tempatkan()` dan menyimpan `placements.penjelasan` (aturan, parameter, rincian per prasyarat, skor minat), profil, dan `enrollments.level_kini/modus_kini`.
- `AdaptationRecorder`: M awal = R/100, memanggil `evaluasi()`, memperbarui `mastery_states`, **hanya `create()`** ke `adaptation_logs`, memperbarui `level_kini`. Model `AdaptationLog` melempar `LogicException` pada `updating`/`deleting` — aturan #2 ditegakkan di lapisan model dan diuji.
- `DifferentiationEngine` terdaftar singleton dengan parameter `config/geulis.php`.
- S-03 Livewire tiga tahap dengan penyimpanan per jawaban (kesiapan → `readiness_responses`, angket → draf `learning_profiles.jawaban_mentah`); melanjutkan dari butir terakhir. S-04 menampilkan titik mulai, cara belajar, artefak.
- A-03 Filament: parameter ditampilkan **baca-saja** dengan peringatan kunci; `GEULIS_PARAMETER_TERKUNCI` ditambahkan ke `config/geulis.php` dan `.env.example`.
- 106 uji hijau; semua butir "Selesai bila" Sprint 1 terpenuhi.

**Tidak selesai**
- Pretest CT belum ada di alur (Sprint 4); penempatan dijalankan langsung setelah angket minat karena penempatan hanya butuh R, P, B.

**Keputusan desain**
- **A-03 tidak menyunting**, hanya menampilkan. Parameter hidup di `config/geulis.php` + `.env` sehingga setiap perubahan lewat Git/jurnal, bukan klik di peramban — jejak revisi yang bisa diperiksa validator. Kunci = variabel lingkungan, bukan tombol.
- **Skor profil 0–100 per modus** dari Likert 1–4: (jumlah − n)/(3n) × 100. Penentuan modus utama/campuran tetap di mesin, bukan di penskor angket, supaya satu aturan satu tempat.
- **Butir angket di berkas konfigurasi**, bukan tabel, karena tetap selama penelitian dan divalidasi ahli bersama instrumen; butir tes kesiapan di seeder (punya tabel) dengan alasan sama.
- **Jawaban asesmen disimpan per butir**, bukan per halaman: syarat "putus koneksi tidak mengulang dari awal".
- Durasi per butir kesiapan dihitung di server (waktu tampil vs waktu jawab) — cukup untuk sinyal, tanpa JavaScript tambahan.

**Sesi berikutnya**
- Sprint 2: A-01 varian konten, `VariantResolver`, S-05, GeoGebra swadaya, aset budaya + atribusi wajib, pengunci pertemuan.

**Commit:** `feat(mesin): asesmen awal, penempatan, dan perekam adaptasi`

## 2026-09-10 · Sprint 0 · kelas, impor CSV, kartu PIN, persetujuan — Sprint 0 selesai

**Selesai**
- Model `Consent`, `Enrollment`; relasi `Classroom::siswa()`, `User::classrooms()`, `kelasDiampu()`, `consent()`; factory `School`, `Classroom`.
- `PembacaCsvSiswa` (murni: koma/titik koma, BOM Excel, judul kolom urutan bebas, galat per baris), `PembangkitPin` (6 digit, tolak pola), `PendaftarSiswa` (akun + peran siswa + kode anonim + enrollment; atur ulang PIN).
- Layar guru Livewire: G-00 beranda (daftar kelas + buat kelas dengan kode gabung otomatis), detail kelas (impor CSV, tambah satu siswa, PIN baru, keluarkan), G-05 kartu PIN cetak (A4, 3 kolom, garis gunting, hitam-putih, tanpa Vite).
- S-02 persetujuan penelitian + middleware `persetujuan` (siswa wajib menjawab sekali; menolak tetap boleh belajar).
- Filament A-02: resource Sekolah dan Kelas berlabel Indonesia, filter kelompok riset, hitung siswa.
- 86 uji hijau. Seluruh butir "Selesai bila" Sprint 0 terpenuhi.

**Tidak selesai**
- "Gabung kelas dengan kode" untuk siswa (opsi pada wireframe S-01) — belum perlu karena siswa didaftarkan guru lewat CSV; dicatat di IDE-TAHUN-DEPAN bila kelak dibutuhkan.

**Keputusan desain**
- **Format CSV**: kolom `nama, nis, jenis_kelamin` (L/P), sesuai brief; PIN **dibangkitkan sistem**, bukan ikut di CSV, supaya guru tidak menyimpan daftar PIN di berkas lepas.
- **PIN disimpan dua kali**: hash di `password` untuk verifikasi, terenkripsi APP_KEY di `pin_kartu` (migrasi baru, bukan menyunting migrasi lama) supaya kartu bisa dicetak ulang kapan saja. Tanpa ini, tiap cetak ulang harus mengatur ulang PIN seluruh kelas — persis gangguan yang ingin dihindari di hari pengambilan data.
- **`kode_anonim` berurutan global** `S-001`… (guru tidak diberi kode; hanya siswa diekspor). Global agar tidak bentrok saat tiga sekolah digabung; sekolah/kelas tetap kolom terpisah saat ekspor.
- Kelas `PendaftarSiswa` menyentuh Eloquent dan tinggal di `app/Services/Kelas/` bersama kelas murni — mengikuti pola `AdaptationRecorder` yang disebut cetak biru: logika murni dan lapisan penyimpanan berdampingan, tetapi kelas murni tidak pernah mengimpor model.
- Kartu PIN tanpa Vite/Tailwind: halaman cetak harus mandiri agar tetap bisa dicetak dari HP guru tanpa aset besar.

**Sesi berikutnya**
- Sprint 1: tes kesiapan, angket profil & minat, `PlacementService`, `AdaptationRecorder`, S-03, A-03.

**Commit:** `feat(guru): kelas, impor csv siswa, kartu pin, dan persetujuan penelitian`

## 2026-09-10 · Sprint 0 · autentikasi NIS + PIN, enam peran, middleware `role:`

**Selesai**
- Enum `App\Enums\Peran` (enam peran §2.3) menjadi satu sumber kebenaran untuk `roles.nama`, seeder, factory, dan alias rute `role:`. `RoleSeeder` idempoten dipanggil `DatabaseSeeder`; akun `admin` seed memegang peran admin + peneliti.
- `User::punyaPeran()`, `User::berikanPeran()`, `User::rutePulang()`; `canAccessPanel()` kini hanya admin/peneliti yang aktif. Filament `UserForm` mendapat pilihan peran (wajib) dan sakelar aktif; tabel pengguna menampilkan lencana peran.
- Middleware `EnsureUserHasRole` dengan alias `role:` (mendukung `role:admin,peneliti`). Tamu diarahkan ke `/masuk`, bukan ke login Filament.
- S-01 Masuk: `LoginController` tipis + `MasukRequest` (validasi, pembatasan laju 5×/menit per username+IP, tolak akun nonaktif, catat `terakhir_masuk_pada`). Rute `/masuk`, `/keluar`, `/login` → `/masuk`; beranda `/` mengarahkan sesuai peran.
- Tata letak dasar `components.layouts.app` (satu kolom, `max-w-md`, `px-4`), tema warna dari wireframe di `app.css`, kerangka `siswa.jalur` dan `guru.beranda`.
- Uji: 58 hijau (25 baru: `Auth/LoginTest`, `RoleMiddlewareTest`, `FilamentAuthTest` diperbarui). Pint bersih. `migrate:fresh --seed` bersih di MariaDB. Vite terbangun.

**Tidak selesai**
- Butir 6–8 Sprint 0 (CRUD sekolah/kelas, impor CSV, G-05 kartu PIN, S-02 persetujuan) — belum dimulai; menunggu alur masuk selesai.
- Pemeriksaan visual 360 px baru lewat struktur (satu kolom, meta viewport, diuji) — belum dibuka di peramban HP sungguhan.
- "Gabung kelas dengan kode" pada wireframe S-01 belum ada tombolnya karena `classrooms` belum punya CRUD; ditambahkan bersama butir 6.

**Keputusan desain**
- Masuk memakai **controller + FormRequest biasa, bukan Livewire**, karena S-01 harus tetap bekerja tanpa JavaScript pada HP kelas bawah dan sinyal sekolah yang buruk; Livewire dipakai untuk layar interaktif setelah masuk.
- Satu kolom `password` menampung PIN siswa maupun sandi guru/admin; pembeda peran ada di tabel `roles`, bukan di jenis kredensial. Formulir masuk tunggal (NIS/NIP + PIN) untuk semua peran, sehingga hanya satu alur yang perlu divalidasi ahli.
- Pesan gagal masuk **tidak membedakan** "NIS tidak ada" dan "PIN salah" — mencegah pengintaian NIS teman sekelas.
- Pembatasan laju masuk **tidak** dimasukkan ke `config/geulis.php`, karena itu parameter keamanan, bukan parameter penelitian yang divalidasi ahli; nilainya konstanta di `MasukRequest`.
- Filament tetap memakai login surel+sandi sendiri di `/admin/login` (pengelola punya surel), sedangkan siswa/guru lewat S-01. `rutePulang()` pengelola mengarah ke `/admin`.
- `welcome.blade.php` bawaan dihapus; `/` bukan halaman pemasaran, hanya pengarah ke layar peran.

**Sesi berikutnya**
- Butir 6: model `Consent`, CRUD sekolah & kelas (Filament A-02), pendaftaran siswa + impor CSV dengan pembangkit PIN dan `kode_anonim`.
- Butir 7–8: G-05 kartu PIN (cetak & atur ulang PIN), S-02 persetujuan penelitian.

**Commit:** lihat `git log --oneline -1` — `feat(auth): alur masuk nis + pin, enam peran, dan middleware role`

## 2026-09-10 · Sprint 0 · pasang kerangka GEULIS dan bahan vibecoding

**Selesai**
- Bahan vibecoding (`CLAUDE.md`, skill `/mulai-sesi` `/sesi-selesai` `/periksa-mutu`, hook commit, aturan Cursor, dokumen sprint/jurnal) terpasang dan disesuaikan dengan Docker yang sebenarnya (`geulis-php`, MariaDB di host, Node di `laravel-node22`).
- Kerangka `03-scaffold` disalin: 6 migrasi (±42 tabel), 5 model, Mesin Diferensiasi, `MotifScorer`, `AikenCalculator`, `NGainCalculator`, `config/geulis.php`, `MeetingSeeder`. Spesifikasi (cetak biru, wireframe, peta rute) di `docs/spesifikasi/`.
- Pest 4 terpasang; 33 uji hijau termasuk 16 kasus `DifferentiationEngineTest`. Pint bersih.
- `migrate:fresh --seed` bersih di MariaDB: 5 pertemuan × 7 unit, 1 admin.
- Laravel Boost 2.8 terpasang (guidelines, skills, MCP lewat `docker exec -i geulis-php`).
- Repo Git diinisialisasi; hook `SessionEnd` diuji.

**Tidak selesai**
- Autentikasi NIS + PIN, peran, middleware `role:` — belum dimulai; itu sasaran sesi berikutnya.
- Model untuk ±30 tabel lain belum dibuat — dibuat saat tabelnya dipakai, supaya setiap model lahir bersama ujinya.

**Keputusan desain**
- Skema `users` kerangka (`nama`, `username` = NIS/NIP, `email` opsional, `kode_anonim`) **menggantikan** skema bawaan Laravel, karena login NIS + PIN dan anonimisasi adalah desain penelitian. Migrasi bawaan disusutkan menjadi `create_sessions_table` (hanya `sessions` + `password_reset_tokens`).
- Filament 5 dipertahankan **hanya** untuk panel admin/peneliti (A-xx): sudah terpasang, berbasis Livewire, MIT, dan bukan bagian klaim orisinalitas HKI. Layar siswa/guru tetap Livewire biasa agar mobile-first dan nada bahasanya sepenuhnya di tangan kita.
- Dua perbaikan pada migrasi kerangka yang hanya muncul di MySQL/MariaDB (lolos di sqlite): kunci asing `rubric_criteria_id` diarahkan eksplisit ke tabel `rubric_criteria` (Laravel menebak `rubric_criterias`), dan indeks unik `validation_ratings` diberi nama pendek karena nama otomatisnya 65 karakter (batas MySQL 64). Pelajaran: `migrate:fresh` di MariaDB wajib masuk ritual `/periksa-mutu`, uji sqlite saja tidak cukup.
- MCP Boost dijalankan di dalam container (`docker exec -i`) supaya konsisten dengan aturan "tidak ada PHP di host" dan bisa menjangkau basis data.
- `DatabaseSeeder` memanggil `MeetingSeeder` supaya `migrate:fresh --seed` langsung menghasilkan kerangka pertemuan — syarat "Selesai bila" Sprint 0.

**Sesi berikutnya**
- Sprint 0 butir 4–5: alur masuk NIS + PIN (S-01), tabel `roles` diisi enam peran, middleware `role:`, dan `canAccessPanel()` dibatasi ke admin/peneliti.
- Uji fitur: masuk, gagal masuk, otorisasi peran.

**Commit:** commit pertama repo, `chore(proyek): pasang kerangka GEULIS dan bahan vibecoding` — hash lihat `git log --reverse --oneline | head -1`

