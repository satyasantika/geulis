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

