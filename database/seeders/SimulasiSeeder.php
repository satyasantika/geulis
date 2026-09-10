<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Models\Classroom;
use App\Models\ContentVariant;
use App\Models\CtTest;
use App\Models\CulturalAsset;
use App\Models\ExpertValidation;
use App\Models\LessonUnit;
use App\Models\Meeting;
use App\Models\Observation;
use App\Models\ObservationItem;
use App\Models\Product;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\ReadinessItem;
use App\Models\Reflection;
use App\Models\Rubric;
use App\Models\RubricScore;
use App\Models\School;
use App\Models\User;
use App\Models\ValidationInstrument;
use App\Services\Asesmen\PencatatTesCt;
use App\Services\Differentiation\AdaptationRecorder;
use App\Services\Differentiation\PlacementService;
use App\Services\Guru\PengesampinganGuru;
use App\Services\Kelas\PendaftarSiswa;
use App\Services\Konten\KemajuanSiswa;
use App\Services\Motif\PenilaiMotif;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * SIMULASI — data realistis untuk melatih dan mendemonstrasikan keenam peran
 * (siswa, guru, validator, observer, peneliti, admin). Bukan untuk server
 * sekolah. Seluruh jejak (placements, adaptation_logs, motif_submissions,
 * ct_scores, aiken_results) dihasilkan lewat layanan asli, bukan disisipkan
 * langsung, supaya konsisten dengan yang akan terjadi di lapangan.
 *
 *   php artisan geulis:seed-simulasi
 *
 * Akun tetap: lihat docs/panduan/README.md dan {@see akunLatihan()}.
 */
class SimulasiSeeder extends Seeder
{
    public const string SANDI_STAF = 'password';

    /**
     * Siswa XI-3 yang PIN-nya dikunci supaya manual bisa dicetak.
     *
     * @var array<string, array{pin: string, catatan: string}>
     */
    public const array SISWA_LATIHAN = [
        '20260301' => ['pin' => '482913', 'catatan' => 'XI-3 eksperimen · 5 pertemuan · angket · produk'],
        '20260313' => ['pin' => '739182', 'catatan' => 'XI-3 · perlu pendampingan + override guru'],
        '20260320' => ['pin' => '615204', 'catatan' => 'XI-3 · belum mulai · layar persetujuan'],
    ];

    /**
     * @return list<array{peran: string, username: string, pin: string, layar: string, manual: string}>
     */
    public static function akunLatihan(): array
    {
        return [
            ['peran' => 'Admin', 'username' => 'admin', 'pin' => self::SANDI_STAF, 'layar' => '/admin', 'manual' => 'docs/panduan/admin.md'],
            ['peran' => 'Peneliti', 'username' => 'peneliti-sim', 'pin' => self::SANDI_STAF, 'layar' => '/riset/kelengkapan', 'manual' => 'docs/panduan/peneliti.md'],
            ['peran' => 'Guru', 'username' => 'guru-sim-1', 'pin' => self::SANDI_STAF, 'layar' => '/guru', 'manual' => 'docs/panduan/guru.md'],
            ['peran' => 'Guru', 'username' => 'guru-sim-2', 'pin' => self::SANDI_STAF, 'layar' => '/guru', 'manual' => 'docs/panduan/guru.md'],
            ['peran' => 'Observer', 'username' => 'observer-sim-1', 'pin' => self::SANDI_STAF, 'layar' => '/observasi', 'manual' => 'docs/panduan/observer.md'],
            ['peran' => 'Observer', 'username' => 'observer-sim-2', 'pin' => self::SANDI_STAF, 'layar' => '/observasi', 'manual' => 'docs/panduan/observer.md'],
        ];
    }

    private const array NAMA_DEPAN = ['Ahmad', 'Siti', 'Reza', 'Nadia', 'Fikri', 'Alya', 'Dimas', 'Putri', 'Rizky', 'Salsa', 'Bagas', 'Nabila', 'Farhan', 'Dewi', 'Ilham', 'Rania', 'Yusuf', 'Zahra', 'Gilang', 'Intan', 'Raka', 'Mutia', 'Aldi', 'Kirana', 'Hafiz', 'Laras', 'Rafi', 'Sinta', 'Bayu', 'Tiara'];

    private const array NAMA_BELAKANG = ['Pratama', 'Nurhaliza', 'Saputra', 'Ramadhan', 'Maulana', 'Anggraeni', 'Wijaya', 'Kusuma', 'Setiawan', 'Lestari', 'Hidayat', 'Permata', 'Nugraha', 'Safitri', 'Firmansyah'];

    private KemajuanSiswa $kemajuan;

    private AdaptationRecorder $recorder;

    private PenilaiMotif $penilaiMotif;

    private PencatatTesCt $pencatatTes;

    public function run(): void
    {
        mt_srand(20260910);

        $this->kemajuan = app(KemajuanSiswa::class);
        $this->recorder = app(AdaptationRecorder::class);
        $this->penilaiMotif = app(PenilaiMotif::class);
        $this->pencatatTes = app(PencatatTesCt::class);

        // Kerangka + konten contoh P1 + akun latihan (guru1, XI MIPA 3).
        $this->call(DemoSeeder::class);
        $this->kontenP2SampaiP5();
        Meeting::query()->update(['terbit' => true]);
        CtTest::query()->update(['aktif' => true]);
        Questionnaire::query()->update(['aktif' => true]);

        $this->akun('admin', 'Admin GEULIS', Peran::Admin, 'admin@geulis.test')->berikanPeran(Peran::Peneliti);
        $peneliti = $this->akun('peneliti-sim', 'Dr. Satya Santika (peneliti)', Peran::Peneliti, 'peneliti@geulis.test');
        $observer1 = $this->akun('observer-sim-1', 'Observer 1 — Rina', Peran::Observer);
        $observer2 = $this->akun('observer-sim-2', 'Observer 2 — Dani', Peran::Observer);

        $tsm = School::query()->firstOrCreate(['npsn' => '20224001'], ['nama' => 'SMAN 2 Tasikmalaya', 'kabupaten_kota' => 'Kota Tasikmalaya']);
        $cms = School::query()->firstOrCreate(['npsn' => '20211002'], ['nama' => 'SMAN 1 Ciamis', 'kabupaten_kota' => 'Ciamis']);
        $guru1 = $this->akun('guru-sim-1', 'Vepi Nurhasanah, S.Pd.', Peran::Guru, 'vepi@geulis.test', $tsm);
        $guru2 = $this->akun('guru-sim-2', 'Asep Ridwan, M.Pd.', Peran::Guru, 'asep@geulis.test', $cms);

        $kelas = [
            $this->kelas($tsm, $guru1, 'XI-3', 'eksperimen', 'SIMTS3', 20260301),
            $this->kelas($tsm, $guru1, 'XI-4', 'kontrol', 'SIMTS4', 20260401),
            $this->kelas($cms, $guru2, 'XI-2', 'eksperimen', 'SIMCM2', 20260201),
            $this->kelas($cms, $guru2, 'XI-5', 'kontrol', 'SIMCM5', 20260501),
        ];
        $this->kunciPinSiswaLatihan();

        $pre = CtTest::query()->where('jenis', 'pretest')->with('items')->firstOrFail();
        $post = CtTest::query()->where('jenis', 'posttest')->with('items')->firstOrFail();
        $meetings = Meeting::query()->with('lessonUnits.activities')->orderBy('urutan')->get();

        foreach ($kelas as $k) {
            $eksperimen = $k->kelompok_riset === 'eksperimen';
            $siswa = $k->siswa()->reorder()->orderBy('username')->get();
            foreach ($siswa as $i => $s) {
                // Persetujuan: satu menolak, satu belum menjawab, sisanya setuju.
                if ($i !== 19) {
                    $s->consent()->firstOrCreate([], ['setuju_data_penelitian' => $i !== 18, 'disetujui_pada' => now()->subDays(30)]);
                }
                if ($i === 19) {
                    continue; // belum mulai sama sekali — muncul di "belum mulai" papan kelas
                }

                $kemampuan = $this->kemampuan($i); // 0..1, dasar semua skor siswa ini
                $this->asesmenAwal($s, $kemampuan);

                // Pretest: dua siswa XI-5 belum mengerjakan → peringatan P-01.
                if (! ($k->nama === 'XI-5' && $i >= 17)) {
                    $this->tesCt($s, $pre, $kemampuan * 0.55, $guru1, $i % 3 !== 0);
                }

                if ($eksperimen) {
                    $jumlahPertemuan = match (true) {
                        $i < 4 => 5,   // lima siswa tuntas → angket muncul
                        $i < 12 => 3,
                        $i < 18 => 1,
                        default => 0,
                    };
                    $this->jalaniPertemuan($s, $meetings, $jumlahPertemuan, $kemampuan, $i);
                    if ($jumlahPertemuan >= 3) {
                        $this->tesCt($s, $post, min(1.0, $kemampuan * 0.55 + 0.35), $guru1, true);
                    }
                    if ($jumlahPertemuan === 5) {
                        $this->angket($s, 'siswa', $kemampuan);
                        $this->produk($s, $meetings->last(), $k->guru, $i < 2);
                    }
                } elseif ($i < 15) {
                    $this->tesCt($s, $post, min(1.0, $kemampuan * 0.55 + 0.12), $guru2, true);
                }
            }
        }

        // Eskalasi & override di XI-3 supaya papan kelas hidup.
        $xi3 = $kelas[0];
        $p2 = $meetings->firstWhere('urutan', 2);
        $pemeriksaanP2 = $p2->lessonUnits->firstWhere('tipe', 'pemeriksaan');
        foreach ($xi3->siswa()->reorder()->orderBy('username')->get()->slice(12, 3) as $s) {
            foreach ([0.1, 0.1, 0.1] as $skor) {
                $this->recorder->evaluasiDanRekam($s, $pemeriksaanP2, $skor, ['durasi_detik' => 240, 'median_durasi_kelas' => 210, 'butir_salah' => [['indeks' => 1, 'label' => 'menukar posisi x dan y']]]);
            }
        }
        $pengesampingan = app(PengesampinganGuru::class);
        $daftar = $xi3->siswa()->reorder()->orderBy('username')->get();
        $pengesampingan->jalankan($guru1, $daftar[12], 'L2', 'Reza sebenarnya paham; ia salah membaca soal karena terburu-buru. Saya sudah membimbingnya langsung.');
        $pengesampingan->jalankan($guru1, $daftar[0], 'L3', 'Ahmad mengerjakan latihan tambahan di rumah dan menjelaskan komposisi transformasi ke temannya dengan benar.');

        $this->validasiAhli($peneliti);
        $this->observasi([$observer1, $observer2], $xi3, $kelas[2], $meetings);
        $this->angket($guru1, 'guru', 0.85);
        $this->angket($guru2, 'guru', 0.7);

        $this->command?->info('Simulasi siap. Akun: docs/panduan/README.md');
    }

    public function kunciPinSiswaLatihan(): void
    {
        foreach (self::SISWA_LATIHAN as $nis => $akun) {
            User::query()->where('username', $nis)->first()?->forceFill([
                'password' => $akun['pin'],
                'pin_kartu' => $akun['pin'],
            ])->save();
        }
    }

    // ------------------------------------------------------------------

    private function akun(string $username, string $nama, Peran $peran, ?string $email = null, ?School $sekolah = null): User
    {
        $u = User::query()->firstOrCreate(['username' => $username], [
            'nama' => $nama, 'email' => $email, 'password' => self::SANDI_STAF, 'school_id' => $sekolah?->id,
        ]);
        $u->berikanPeran($peran);

        return $u;
    }

    private function kelas(School $sekolah, User $guru, string $nama, string $kelompok, string $kode, int $nisAwal): Classroom
    {
        $kelas = Classroom::query()->firstOrCreate(['school_id' => $sekolah->id, 'nama' => $nama], [
            'guru_id' => $guru->id, 'tahun_ajaran' => '2026/2027', 'kelompok_riset' => $kelompok, 'kode_gabung' => $kode,
        ]);

        if ($kelas->siswa()->count() > 0) {
            return $kelas;
        }

        $baris = [];
        for ($i = 0; $i < 20; $i++) {
            $baris[] = [
                'nama' => self::NAMA_DEPAN[($i * 7 + $nisAwal) % 30].' '.self::NAMA_BELAKANG[($i * 3 + $nisAwal) % 15],
                'nis' => (string) ($nisAwal + $i),
                'jenis_kelamin' => $i % 2 ? 'P' : 'L',
            ];
        }
        app(PendaftarSiswa::class)->daftarkanBanyak($kelas, $baris);

        // Siswa pertama tiap kelas diberi PIN tetap untuk simulasi.
        $pertama = User::query()->where('username', (string) $nisAwal)->firstOrFail();
        $pertama->forceFill(['password' => '482913', 'pin_kartu' => '482913'])->save();

        return $kelas;
    }

    /** Kemampuan deterministik 0,25–0,95 dari urutan siswa di kelas. */
    private function kemampuan(int $i): float
    {
        return round(0.25 + (($i * 37) % 71) / 100, 2);
    }

    private function asesmenAwal(User $siswa, float $kemampuan): void
    {
        $butir = ReadinessItem::query()->orderBy('urutan')->get();
        $benar = (int) round($kemampuan * 15);
        foreach ($butir as $j => $b) {
            $ok = $j < $benar;
            $siswa->readinessResponses()->firstOrCreate(
                ['readiness_item_id' => $b->id],
                [
                    'jawaban' => $ok ? $b->kunci : ($b->kunci + 1) % 4,
                    'benar' => $ok,
                    'durasi_detik' => 30 + mt_rand(0, 90),
                ],
            );
        }

        if ($siswa->placement()->exists()) {
            return;
        }

        $modusDominan = ['visual', 'simbolik', 'naratif'][mt_rand(0, 2)];
        $profil = array_map(fn (array $b) => $b['modus'] === $modusDominan ? mt_rand(3, 4) : mt_rand(1, 3), config('angket.profil'));
        $artefak = ['batik', 'payung_geulis', 'anyaman'][mt_rand(0, 2)];
        $minat = array_map(fn (array $b) => $b['artefak'] === $artefak ? 4 : mt_rand(1, 3), config('angket.minat'));

        app(PlacementService::class)->tempatkan($siswa, $profil, $minat);
    }

    private function jalaniPertemuan(User $siswa, $meetings, int $jumlah, float $kemampuan, int $indeks): void
    {
        foreach ($meetings->take($jumlah) as $m) {
            foreach ($m->lessonUnits as $u) {
                match ($u->tipe) {
                    'pemeriksaan' => $this->pemeriksaan($siswa, $u, $kemampuan),
                    'motif' => $this->motif($siswa, $u, $kemampuan, $indeks),
                    'refleksi' => $this->refleksi($siswa, $m),
                    'latihan' => $this->latihan($siswa, $u, $kemampuan),
                    default => null,
                };
                $this->kemajuan->tandaiSelesai($siswa, $u);
            }
        }
    }

    private function pemeriksaan(User $siswa, LessonUnit $unit, float $kemampuan): void
    {
        $aktivitas = $unit->activities->firstWhere('tipe', 'kuis');
        $skor = max(0.0, min(1.0, $kemampuan + (mt_rand(-20, 20) / 100)));
        $durasi = 120 + (int) ((1 - $kemampuan) * 300) + mt_rand(0, 60);

        if ($aktivitas) {
            ActivityAttempt::query()->create([
                'user_id' => $siswa->id, 'activity_id' => $aktivitas->id, 'percobaan_ke' => 1,
                'skor' => round($skor * 100, 2), 'durasi_detik' => $durasi,
                'mulai_pada' => now()->subDays(mt_rand(1, 20))->subSeconds($durasi), 'selesai_pada' => now()->subDays(mt_rand(1, 20)),
            ]);
        }

        $keputusan = $this->recorder->evaluasiDanRekam($siswa, $unit, $skor, [
            'durasi_detik' => $durasi, 'median_durasi_kelas' => 260,
            'butir_salah' => $skor < 0.75 ? [['indeks' => 1, 'label' => 'menukar posisi x dan y']] : [],
        ]);

        // Remedial: coba sekali lagi dengan hasil lebih baik (perancah membantu).
        if ($keputusan->kodeAturan === 'RULE_REMEDIATE') {
            $this->recorder->evaluasiDanRekam($siswa, $unit, min(1.0, $skor + 0.35), ['durasi_detik' => $durasi + 60, 'median_durasi_kelas' => 260]);
        }
    }

    private function latihan(User $siswa, LessonUnit $unit, float $kemampuan): void
    {
        $aktivitas = $unit->activities->firstWhere('tipe', 'kuis');
        if ($aktivitas === null) {
            return;
        }
        ActivityAttempt::query()->create([
            'user_id' => $siswa->id, 'activity_id' => $aktivitas->id, 'percobaan_ke' => 1,
            'skor' => round(min(1, $kemampuan + 0.1) * 100, 2), 'durasi_detik' => 200, 'mulai_pada' => now()->subDays(5), 'selesai_pada' => now()->subDays(5),
        ]);
    }

    private function motif(User $siswa, LessonUnit $unit, float $kemampuan, int $indeks): void
    {
        $aktivitas = $unit->activities->firstWhere('tipe', 'motif_builder');
        if ($aktivitas === null) {
            return;
        }
        $sasaran = $aktivitas->konfigurasi['sasaran'];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect x="30" y="30" width="40" height="40" fill="#7c2d12" fill-opacity=".7"/></svg>';

        // Percobaan pertama: siswa lemah salah parameter; sedang: versi panjang (tanpa ULANGI); kuat: langsung benar.
        if ($kemampuan < 0.45) {
            $salah = $this->ubahParameter($sasaran);
            $this->penilaiMotif->nilai($siswa, $aktivitas, $salah, [0, 1], $svg);
        }
        $perintah = $kemampuan < 0.7 ? $this->tanpaPerulangan($sasaran) : $sasaran;
        $this->penilaiMotif->nilai($siswa, $aktivitas, $perintah, [$indeks % 2 === 0 ? 0 : 1], $svg);
    }

    /** Membuka ULANGI menjadi perintah berulang — benar, tetapi efisiensi rendah. */
    private function tanpaPerulangan(array $perintah): array
    {
        $hasil = [];
        foreach ($perintah as $p) {
            if (($p['op'] ?? '') === 'ULANGI') {
                for ($i = 0; $i < (int) $p['n']; $i++) {
                    $hasil = [...$hasil, ...$this->tanpaPerulangan($p['badan'])];
                }
            } else {
                $hasil[] = $p;
            }
        }

        return $hasil;
    }

    private function ubahParameter(array $perintah): array
    {
        return array_map(function (array $p): array {
            if (($p['op'] ?? '') === 'ULANGI') {
                $p['badan'] = $this->ubahParameter($p['badan']);
            }
            if (isset($p['sudut'])) {
                $p['sudut'] = $p['sudut'] * 2;
            }
            if (isset($p['vektor'])) {
                $p['vektor'] = [$p['vektor'][0] + 1, $p['vektor'][1]];
            }
            if (isset($p['k'])) {
                $geser = $p['k'] + 1;
                $p['k'] = $geser == 0.0 ? 2 : $geser;
            }

            return $p;
        }, $perintah);
    }

    private function refleksi(User $siswa, Meeting $m): void
    {
        $jawaban = [
            ['Bagian mana yang paling membuatmu paham hari ini, dan mengapa?', 'Waktu menggeser motif di GeoGebra, saya lihat sendiri semua titik pindah sejauh anak panah yang sama.'],
            ['Kalau kamu menjelaskan materi ini ke teman, apa yang akan kamu katakan pertama kali?', 'Cari dulu satuan terkecil yang berulang, baru pikirkan perintah apa yang memindahkannya.'],
        ];
        foreach ($jawaban as [$p, $j]) {
            Reflection::query()->firstOrCreate(['user_id' => $siswa->id, 'meeting_id' => $m->id, 'pertanyaan' => $p], ['jawaban' => $j.' ('.ucfirst($m->materi).')']);
        }
    }

    private function tesCt(User $siswa, CtTest $tes, float $peluangBenar, User $penilai, bool $nilaiUraian): void
    {
        $jawaban = [];
        foreach ($tes->items as $b) {
            if ($b->tipe === 'pg') {
                $benar = mt_rand(0, 100) / 100 < $peluangBenar;
                $jawaban[$b->id] = $benar ? $b->kunci : collect($b->pilihan)->first(fn ($p) => $p !== $b->kunci);
            } else {
                $jawaban[$b->id] = $peluangBenar > 0.4
                    ? 'Motif bisa dipecah menjadi satu bagian yang berulang, lalu dibentuk kembali dengan transformasi yang sama berkali-kali.'
                    : 'Digambar semua saja satu per satu.';
            }
        }
        $this->pencatatTes->kumpulkan($siswa, $tes, $jawaban);

        if ($nilaiUraian) {
            foreach ($siswa->ctResponses()->whereIn('ct_item_id', $tes->items->where('tipe', 'uraian')->pluck('id'))->get() as $r) {
                $skor = (float) max(0, min(4, (int) round($peluangBenar * 4) + mt_rand(-1, 1)));
                $this->pencatatTes->nilaiUraian($r, $penilai, $skor, $skor >= 3 ? null : 'Sebutkan transformasinya dengan nama dan parameternya.');
            }
        }
    }

    private function angket(User $user, string $sasaran, float $kepuasan): void
    {
        $angket = Questionnaire::query()->where('sasaran', $sasaran)->with('items')->first();
        if ($angket === null) {
            return;
        }
        foreach ($angket->items as $i) {
            $skor = $kepuasan > 0.6 ? mt_rand(3, 4) : mt_rand(2, 3);
            if ($i->butir_negatif) {
                $skor = 5 - $skor;
            }
            QuestionnaireResponse::query()->updateOrCreate(['user_id' => $user->id, 'questionnaire_item_id' => $i->id], ['skor' => $skor]);
        }
    }

    private function produk(User $siswa, Meeting $m, User $guru, bool $dinilai): void
    {
        $bentuk = array_keys(Product::BENTUK)[$siswa->id % 4];
        $produk = Product::query()->firstOrCreate(['user_id' => $siswa->id, 'meeting_id' => $m->id], [
            'bentuk' => $bentuk,
            'tautan' => $bentuk === 'video' ? 'https://youtu.be/simulasi-'.$siswa->id : null,
            'berkas' => null,
            'deskripsi' => 'Saya membedah rozet payung geulis 8 jari-jari menjadi satu kelopak, lalu ULANGI 8 KALI ROTASI 45° dengan pusat O. Komposisi geser-lalu-putar saya bandingkan dengan putar-lalu-geser: hasilnya berbeda.',
            'dikirim_pada' => now()->subDays(2),
        ]);

        if ($dinilai) {
            $rubrik = Rubric::query()->with('criteria')->where('untuk', 'produk_akhir')->first();
            foreach ($rubrik?->criteria ?? [] as $j => $k) {
                RubricScore::query()->updateOrCreate(['product_id' => $produk->id, 'rubric_criteria_id' => $k->id], [
                    'penilai_id' => $guru->id, 'tingkat' => [4, 3, 4, 3][$j], 'umpan_balik' => $j === 1 ? 'Perulangan sudah dipakai; jelaskan mengapa 8 × 45° = 360°.' : null,
                ]);
            }
        }
    }

    private function validasiAhli(User $peneliti): void
    {
        $instrumen = ValidationInstrument::query()->with('items')->firstOrFail();
        $validator = [
            ['validator-sim-1', 'Prof. Dr. Ahli Materi', [5, 4, 5], true],
            ['validator-sim-2', 'Dr. Ahli Media Pembelajaran', [4, 4, 5], true],
            ['validator-sim-3', 'Guru Praktisi — Dra. Euis', [4, 3, 4], true],
            ['validator-sim-4', 'Dr. Ahli Etnomatematika', [], false],
        ];
        foreach ($validator as [$username, $nama, $pola, $selesai]) {
            $u = $this->akun($username, $nama, Peran::Validator, $username.'@kampus.ac.id');
            $v = ExpertValidation::query()->firstOrCreate(['validator_id' => $u->id, 'validation_instrument_id' => $instrumen->id]);
            if (! $selesai) {
                continue;
            }
            foreach ($instrumen->items as $i => $item) {
                $skor = $pola[$i % 3];
                if ($item->urutan === 12) {
                    $skor = 3; // "α = 0,4 sudah wajar" — sedang
                }
                if ($item->urutan === 5) {
                    $skor = $username === 'validator-sim-1' ? 2 : 3; // butir pemeriksaan → rendah, perlu revisi
                }
                $saran = match (true) {
                    $item->urutan === 5 && $username === 'validator-sim-1' => 'Butir 3 pemeriksaan P2 mengukur hitungan, bukan abstraksi.',
                    $item->urutan === 9 && $username === 'validator-sim-3' => 'L1 masih agak melompat ke matriks pada unit 2.3.',
                    $item->urutan === 22 && $username === 'validator-sim-2' => 'Tombol Motif Builder pada HP 360 px perlu lebih tinggi.',
                    default => null,
                };
                $v->ratings()->updateOrCreate(['validation_item_id' => $item->id], ['skor' => $skor, 'saran' => $saran]);
            }
            $v->update(['status' => 'selesai', 'selesai_pada' => now()->subDays(3)]);
        }
    }

    private function observasi(array $observer, Classroom $xi3, Classroom $xi2, $meetings): void
    {
        $butir = ObservationItem::query()->pluck('id');
        foreach ([[$xi3, 1], [$xi3, 2], [$xi3, 3], [$xi2, 1]] as $k => [$kelas, $urutan]) {
            $o = $observer[$k % 2];
            $m = $meetings->firstWhere('urutan', $urutan);
            $obs = Observation::query()->firstOrCreate(
                ['observer_id' => $o->id, 'classroom_id' => $kelas->id, 'meeting_id' => $m->id, 'tanggal' => now()->subDays(20 - $urutan * 5)->toDateString()],
                ['catatan_lapangan' => $urutan === 3 ? '6 siswa memakai HP berlayar kecil; Motif Builder perlu diperbesar dua jari. Guru mendatangi dua siswa dari daftar pendampingan.' : 'Berjalan lancar; Wi-Fi sempat putus 3 menit, siswa melanjutkan tanpa kehilangan jawaban.'],
            );
            foreach ($butir as $id) {
                $obs->records()->updateOrCreate(['observation_item_id' => $id], ['skor' => mt_rand(3, 4)]);
            }
        }
    }

    // ------------------------------------------------------------------

    private function kontenP2SampaiP5(): void
    {
        Storage::disk('public')->put('aset-budaya/contoh-batik.svg', file_get_contents(__DIR__.'/data/contoh-batik.svg'));
        Storage::disk('public')->put('aset-budaya/contoh-payung.svg', file_get_contents(__DIR__.'/data/contoh-payung.svg'));

        $asetBatik = CulturalAsset::query()->firstOrCreate(['nama_motif' => 'Batik Sawoan (contoh)'], [
            'artefak' => 'batik', 'perajin_sumber' => 'Ibu Enok S. (contoh)', 'lokasi' => 'Cigeureung, Kota Tasikmalaya', 'tanggal_dokumentasi' => '2026-05-12',
            'izin_diperoleh' => true, 'berkas' => 'aset-budaya/contoh-batik.svg', 'catatan_matematis' => 'Motif daun bersimetri lipat terhadap dua sumbu (tegak dan mendatar).',
        ]);
        $asetPayung = CulturalAsset::query()->firstOrCreate(['nama_motif' => 'Rozet Payung Geulis (contoh)'], [
            'artefak' => 'payung_geulis', 'perajin_sumber' => 'Pak Dadan (contoh)', 'lokasi' => 'Panyingkiran, Kota Tasikmalaya', 'tanggal_dokumentasi' => '2026-05-14',
            'izin_diperoleh' => true, 'berkas' => 'aset-budaya/contoh-payung.svg', 'catatan_matematis' => 'Simetri putar tingkat 8: sudut putar 360°/8 = 45°.',
        ]);

        $konten = [
            2 => ['aset' => $asetBatik, 'jangkar' => 'Kalau kain ini kamu lipat, di mana lipatannya supaya kedua sisi motifnya bertemu tepat? Ada berapa lipatan yang mungkin?',
                'formal' => ['L1' => 'Refleksi = mencerminkan. Titik (x, y) dicerminkan pada sumbu-x menjadi (x, −y); pada sumbu-y menjadi (−x, y). Coba lipat kertasmu!', '*' => 'Refleksi terhadap garis y = x: (x, y) ⟼ (y, x). Dalam bentuk matriks M = [0 1; 1 0]. Pada motif sawoan, sumbu simetri diagonal motif inilah garis y = x.', 'L3' => 'Komposisi dua refleksi pada garis yang berpotongan adalah rotasi dua kali sudut antara garisnya. Buktikan untuk sumbu-x lalu y = x.'],
                'kuis' => [['Bayangan (2, 3) oleh refleksi terhadap sumbu-x adalah …', ['(2, −3)', '(−2, 3)', '(3, 2)', '(−2, −3)'], 0, 'tanda ordinat'], ['Bayangan (2, 3) oleh refleksi terhadap garis y = x adalah …', ['(3, 2)', '(−3, −2)', '(2, −3)', '(−2, 3)'], 0, 'menukar posisi x dan y'], ['Bayangan (−1, 4) oleh refleksi terhadap garis y = −x adalah …', ['(−4, 1)', '(4, −1)', '(1, −4)', '(−1, −4)'], 0, 'refleksi y = −x'], ['Banyak sumbu simetri motif sawoan pada gambar adalah …', ['2', '1', '4', '0'], 0, 'menghitung sumbu simetri']]],
            3 => ['aset' => $asetPayung, 'jangkar' => 'Hitung jari-jari payung ini. Kalau payung diputar sedikit saja, kapan hiasannya kembali tampak sama persis?',
                'formal' => ['L1' => 'Rotasi = memutar terhadap satu titik pusat. Putaran 90° berlawanan arah jarum jam memindahkan (x, y) ke (−y, x).', '*' => 'Rotasi θ dengan pusat O: (x, y) ⟼ (x cos θ − y sin θ, x sin θ + y cos θ). Payung dengan n jari-jari punya simetri putar 360°/n.', 'L3' => 'Rotasi dengan pusat P ≠ O = translasi ke O, rotasi, translasi balik. Turunkan rumusnya dan uji pada kelopak payung.'],
                'kuis' => [['Sudut putar terkecil payung 8 jari-jari adalah …', ['45°', '90°', '30°', '60°'], 0, 'sudut 360°/n'], ['Bayangan (3, 0) oleh rotasi 90° pusat O adalah …', ['(0, 3)', '(−3, 0)', '(0, −3)', '(3, 3)'], 0, 'rotasi 90°'], ['Rotasi 180° memetakan (a, b) ke …', ['(−a, −b)', '(b, a)', '(−a, b)', '(a, −b)'], 0, 'rotasi 180°'], ['ULANGI 6 KALI ROTASI 60° menghasilkan berapa kelopak?', ['6', '5', '12', '3'], 0, 'banyak pengulangan']]],
            4 => ['aset' => $asetPayung, 'jangkar' => 'Motif di tengah payung tampak lebih kecil daripada di tepi. Berapa kali lebih besar? Dari titik mana pembesarannya diukur?',
                'formal' => ['L1' => 'Dilatasi = memperbesar/memperkecil dari satu titik pusat. Dengan pusat O dan faktor k: (x, y) ⟼ (kx, ky). k = 2 berarti dua kali lebih besar.', '*' => 'Dilatasi pusat P(a, b), faktor k: (x, y) ⟼ (a + k(x − a), b + k(y − b)). 0 < k < 1 memperkecil; k < 0 membalik ke sisi lain pusat.', 'L3' => 'Dilatasi mempertahankan kesebangunan tetapi bukan isometri. Bandingkan luas motif sebelum dan sesudah: berapa faktornya?'],
                'kuis' => [['Bayangan (1, 2) oleh dilatasi pusat O faktor 3 adalah …', ['(3, 6)', '(4, 5)', '(1/3, 2/3)', '(−3, −6)'], 0, 'mengalikan faktor'], ['Dilatasi faktor k = −1 pusat O sama dengan …', ['rotasi 180°', 'refleksi sumbu-x', 'translasi', 'refleksi sumbu-y'], 0, 'k negatif'], ['Faktor 0,5 membuat motif …', ['setengah ukuran', 'dua kali ukuran', 'terbalik', 'bergeser'], 0, '0 < k < 1'], ['Bayangan (4, 4) oleh dilatasi pusat (2, 2) faktor 2 adalah …', ['(6, 6)', '(8, 8)', '(3, 3)', '(4, 4)'], 0, 'pusat bukan O']]],
            5 => ['aset' => $asetBatik, 'jangkar' => 'Pilih artefak favoritmu. Perintah apa saja, dalam urutan apa, yang membangunnya? Apakah urutannya boleh ditukar?',
                'formal' => ['L1' => 'Komposisi = melakukan dua transformasi berurutan. Geser dulu lalu putar, hasilnya bisa berbeda dari putar dulu lalu geser. Coba di Motif Builder!', '*' => '(T₂ ∘ T₁)(P) = T₂(T₁(P)): T₁ dulu, baru T₂. Komposisi umumnya tidak komutatif. Dalam matriks: M = M₂ · M₁.', 'L3' => 'Tentukan matriks komposisi refleksi y = x lalu rotasi 90°. Apakah sama dengan urutan sebaliknya? Kaitkan dengan motif orisinalmu.'],
                'kuis' => [['(2, 0) digeser (1, 0) lalu diputar 90° pusat O menjadi …', ['(0, 3)', '(1, 2)', '(0, 2)', '(3, 0)'], 0, 'urutan komposisi'], ['(2, 0) diputar 90° lalu digeser (1, 0) menjadi …', ['(1, 2)', '(0, 3)', '(3, 0)', '(−1, 2)'], 0, 'urutan komposisi'], ['Komposisi dua translasi (a, b) lalu (c, d) setara translasi …', ['(a + c, b + d)', '(ac, bd)', '(a − c, b − d)', '(c, d)'], 0, 'komposisi translasi'], ['Algoritma yang paling ringkas untuk 12 kelopak adalah …', ['ULANGI 12 KALI ROTASI 30°', '12 perintah ROTASI 30°', 'ULANGI 6 KALI ROTASI 30°', 'ROTASI 360°'], 0, 'memakai perulangan']]],
        ];

        foreach ($konten as $urutan => $k) {
            $m = Meeting::query()->where('urutan', $urutan)->firstOrFail();
            $unit = fn (string $tipe): LessonUnit => $m->lessonUnits()->where('tipe', $tipe)->firstOrFail();
            $simpan = fn (LessonUnit $u, string $level, string $judul, string $badan, array $ekstra = []) => ContentVariant::query()->updateOrCreate(
                ['lesson_unit_id' => $u->id, 'level' => $level, 'modus' => '*'], ['judul' => $judul, 'badan_konten' => '<p>'.$badan.'</p>', 'status' => 'siap'] + $ekstra);

            $simpan($unit('jangkar'), '*', 'Jangkar budaya: '.$k['aset']->nama_motif, $k['jangkar'], ['cultural_asset_id' => $k['aset']->id]);
            $simpan($unit('eksplorasi'), '*', 'Eksplorasi '.ucfirst($m->materi), 'Coba di GeoGebra: ubah parameternya dan perhatikan ke mana motif berpindah.', [
                'konfigurasi_geogebra' => ['app' => 'geometry', 'terpandu' => true, 'perintah' => "A=(1,1)\nB=(3,1)\nC=(2,3)\nmotif=Polygon(A,B,C)\n".match ($m->materi) {
                    'refleksi' => "g: y = x\nmotif'=Reflect(motif, g)", 'rotasi' => "motif'=Rotate(motif, 45°, (0,0))", 'dilatasi' => "motif'=Dilate(motif, 2, (0,0))", default => "motif'=Rotate(Translate(motif, Vector((2,0))), 90°, (0,0))"
                }],
            ]);
            foreach ($k['formal'] as $level => $badan) {
                $simpan($unit('formalisasi'), $level, 'Formalisasi '.ucfirst($m->materi).($level === '*' ? '' : " ($level)"), $badan);
            }
            $butir = array_map(fn ($b) => ['pertanyaan' => $b[0], 'pilihan' => $b[1], 'kunci' => $b[2], 'label' => $b[3]], $k['kuis']);
            Activity::query()->updateOrCreate(['lesson_unit_id' => $unit('latihan')->id, 'tipe' => 'kuis'], ['judul' => 'Latihan '.ucfirst($m->materi), 'konfigurasi' => ['butir' => array_slice($butir, 0, 3)]]);
            Activity::query()->updateOrCreate(['lesson_unit_id' => $unit('pemeriksaan')->id, 'tipe' => 'kuis'], ['judul' => 'Pemeriksaan Penguasaan: '.ucfirst($m->materi), 'konfigurasi' => ['butir' => $butir]]);
            Activity::query()->updateOrCreate(['lesson_unit_id' => $unit('refleksi')->id, 'tipe' => 'refleksi'], ['judul' => 'Refleksi Pertemuan '.$urutan, 'konfigurasi' => ['pertanyaan' => [
                'Bagian mana yang paling membuatmu paham hari ini, dan mengapa?', 'Kalau kamu menjelaskan materi ini ke teman, apa yang akan kamu katakan pertama kali?',
            ]]]);
        }
    }
}
