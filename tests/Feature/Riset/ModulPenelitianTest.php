<?php

use App\Livewire\Angket\AngketRespons;
use App\Livewire\Observasi\LembarObservasi;
use App\Livewire\Peneliti\Analitik;
use App\Livewire\Peneliti\Ekspor;
use App\Livewire\Peneliti\Kelengkapan;
use App\Livewire\Siswa\Jalur;
use App\Models\Classroom;
use App\Models\CtItem;
use App\Models\CtTest;
use App\Models\DataExport;
use App\Models\Meeting;
use App\Models\Observation;
use App\Models\ObservationItem;
use App\Models\Questionnaire;
use App\Models\User;
use App\Services\Asesmen\PencatatTesCt;
use App\Services\Differentiation\AdaptationRecorder;
use App\Services\Konten\KemajuanSiswa;
use App\Services\Research\AnalitikPenelitian;
use Database\Seeders\InstrumenPenelitianSeeder;
use Database\Seeders\MeetingSeeder;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->seed([MeetingSeeder::class, InstrumenPenelitianSeeder::class]);
    Meeting::query()->update(['terbit' => true]);
    $this->peneliti = User::factory()->peneliti()->create();

    $this->eksperimen = Classroom::factory()->eksperimen()->create(['nama' => 'XI-3']);
    $this->kontrol = Classroom::factory()->kontrol()->create(['nama' => 'XI-4']);

    $this->buatSiswa = function (Classroom $kelas, string $nama, bool $setuju = true, ?string $kode = null): User {
        $s = User::factory()->siswa()->create(['nama' => $nama, 'kode_anonim' => $kode ?? User::kodeAnonimBerikutnya('S')]);
        $s->consent()->create(['setuju_data_penelitian' => $setuju, 'disetujui_pada' => now()]);
        $kelas->enrollments()->create(['user_id' => $s->id]);
        $s->placement()->create(['skor_readiness' => 70, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);

        return $s;
    };
});

function kerjakanTes(User $siswa, CtTest $tes, float $persenBenar): void
{
    // Semua butir PG: jawab benar sebanyak proporsi yang diminta.
    $jawaban = [];
    $n = $tes->items->count();
    foreach ($tes->items as $i => $item) {
        $jawaban[$item->id] = $i < (int) round($persenBenar * $n) ? $item->kunci : 'salah';
    }
    app(PencatatTesCt::class)->kumpulkan($siswa, $tes, $jawaban);
}

describe('angket respons', function (): void {
    it('appears on the student path only after all five meetings and stores answers', function (): void {
        $siswa = ($this->buatSiswa)($this->eksperimen, 'Siti');
        Questionnaire::query()->where('sasaran', 'siswa')->update(['aktif' => true]);

        Livewire::actingAs($siswa)->test(Jalur::class)->assertDontSee('Isi angket respons');

        $kemajuan = app(KemajuanSiswa::class);
        foreach (Meeting::query()->with('lessonUnits')->get() as $m) {
            foreach ($m->lessonUnits as $u) {
                $kemajuan->tandaiSelesai($siswa, $u);
            }
        }

        Livewire::actingAs($siswa)->test(Jalur::class)->assertSee('Isi angket respons');

        $angket = Questionnaire::query()->where('sasaran', 'siswa')->firstOrFail();
        $komponen = Livewire::actingAs($siswa)->test(AngketRespons::class, ['sasaran' => 'siswa'])
            ->set("jawaban.{$angket->items[0]->id}", 4)
            ->call('kirim')
            ->assertHasErrors(['kirim']);

        expect($siswa->questionnaireResponses()->count())->toBe(1);

        foreach ($angket->items as $i) {
            $komponen->set("jawaban.{$i->id}", $i->butir_negatif ? 1 : 4);
        }
        $komponen->call('kirim')->assertHasNoErrors()->assertSee('sudah terkirim');

        expect($siswa->questionnaireResponses()->count())->toBe(12);

        Livewire::actingAs($this->peneliti)->test(Analitik::class)->assertSee('sangat praktis');
    });

    it('serves the teacher questionnaire to teachers only', function (): void {
        Questionnaire::query()->where('sasaran', 'guru')->update(['aktif' => true]);

        actingAs($this->eksperimen->guru)->get(route('guru.angket'))->assertOk()->assertSee('Angket Respons Guru');
        actingAs(User::factory()->siswa()->create())->get(route('guru.angket'))->assertForbidden();
    });
});

describe('O-01 observasi', function (): void {
    it('accepts a complete sheet from an observer and rejects an incomplete one', function (): void {
        $observer = User::factory()->observer()->create();
        $skor = ObservationItem::query()->pluck('id')->mapWithKeys(fn ($id) => [$id => 3])->all();
        $p3 = Meeting::query()->where('urutan', 3)->firstOrFail();

        $komponen = Livewire::actingAs($observer)->test(LembarObservasi::class)
            ->call('kirim', ['classroom_id' => $this->eksperimen->id, 'meeting_id' => $p3->id, 'tanggal' => '2026-10-14', 'skor' => array_slice($skor, 0, 3, true)])
            ->assertReturned(['ok' => false, 'pesan' => 'Semua butir harus diberi skor 1–4.']);

        expect(Observation::query()->count())->toBe(0);

        $komponen->call('kirim', ['classroom_id' => $this->eksperimen->id, 'meeting_id' => $p3->id, 'tanggal' => '2026-10-14', 'skor' => $skor, 'catatan' => '6 siswa memakai HP berlayar kecil.'])
            ->assertReturned(['ok' => true, 'pesan' => 'Terkirim.']);

        $observasi = Observation::query()->firstOrFail();

        expect($observasi->records()->count())->toBe(10)
            ->and($observasi->catatan_lapangan)->toContain('berlayar kecil')
            ->and($observasi->observer_id)->toBe($observer->id);

        // Kirim ulang (antrean luring yang terkirim dua kali) tidak menggandakan lembar.
        $komponen->call('kirim', ['classroom_id' => $this->eksperimen->id, 'meeting_id' => $p3->id, 'tanggal' => '2026-10-14', 'skor' => $skor]);
        expect(Observation::query()->count())->toBe(1);

        actingAs($observer)->get(route('observasi.form'))->assertOk()->assertSee('Mode luring')->assertSee('localStorage', escape: false);
    });

    it('sends an observer to the observation sheet after login', function (): void {
        actingAs(User::factory()->observer()->create())->get('/')->assertRedirect(route('observasi.form'));
    });
});

describe('P-01 kelengkapan & P-02 analitik', function (): void {
    it('counts completeness per research class and warns about missing pretests', function (): void {
        $pre = CtTest::factory()->denganButir()->create();
        $a = ($this->buatSiswa)($this->eksperimen, 'A');
        $b = ($this->buatSiswa)($this->eksperimen, 'B');
        kerjakanTes($a, $pre, 1.0);

        Livewire::actingAs($this->peneliti)->test(Kelengkapan::class)
            ->assertSee('XI-3')
            ->assertSee('2/2')      // asesmen awal
            ->assertSee('1/2')      // pretest
            ->assertSee('1 siswa belum mengerjakan pretest');
    });

    it('computes N-Gain per group and per indicator for consenting students only', function (): void {
        $pre = CtTest::factory()->create(['jenis' => 'pretest']);
        $post = CtTest::factory()->posttest()->create();
        foreach ([$pre, $post] as $t) {
            foreach (['D', 'P', 'A', 'Al'] as $i => $ind) {
                CtItem::factory()->for($t, 'test')->create(['urutan' => $i + 1, 'indikator' => $ind, 'tipe' => 'pg', 'pilihan' => ['a', 'b'], 'kunci' => 'a']);
            }
            $t->load('items');
        }

        $e1 = ($this->buatSiswa)($this->eksperimen, 'E1');
        $e2 = ($this->buatSiswa)($this->eksperimen, 'E2');
        $k1 = ($this->buatSiswa)($this->kontrol, 'K1');
        $tolak = ($this->buatSiswa)($this->eksperimen, 'Menolak', setuju: false);

        kerjakanTes($e1, $pre, 0.25);
        kerjakanTes($e1, $post, 1.0);   // gain (100−25)/(100−25) = 1,0
        kerjakanTes($e2, $pre, 0.5);
        kerjakanTes($e2, $post, 0.75);   // (75−50)/(100−50) = 0,5
        kerjakanTes($k1, $pre, 0.5);
        kerjakanTes($k1, $post, 0.5);    // 0
        kerjakanTes($tolak, $pre, 0.0);
        kerjakanTes($tolak, $post, 1.0); // dikeluarkan

        $analitik = app(AnalitikPenelitian::class);
        $baris = $analitik->perSiswa();
        $kelompok = $analitik->perKelompok($baris);

        expect($baris)->toHaveCount(3)
            ->and($kelompok['eksperimen']['n'])->toBe(2)
            ->and($kelompok['eksperimen']['rerata'])->toBe(0.75)
            ->and($kelompok['eksperimen']['kategori'])->toBe('tinggi')
            ->and($kelompok['kontrol']['rerata'])->toBe(0.0)
            ->and($analitik->perIndikator($baris)['Al']['ngain_e'])->toBe(0.5)   // E1: 0→100 = 1,0; E2: 0→0 = 0
            ->and($analitik->perIndikator($baris)['D']['ngain_e'])->toBeNull(); // pretest D sudah 100 → tak terdefinisi

        Livewire::actingAs($this->peneliti)->test(Analitik::class)->assertSee('0,75')->assertSee('Uji-t dijalankan di SPSS/JASP');
    });
});

describe('P-03 ekspor', function (): void {
    it('writes an anonymised multi-sheet xlsx without a single student name or NIS', function (): void {
        $pre = CtTest::factory()->denganButir()->create();
        $siswa = ($this->buatSiswa)($this->eksperimen, 'Reza Pratama Unik', kode: 'S-777');
        $siswa->update(['username' => '0099887766']);
        ($this->buatSiswa)($this->eksperimen, 'Nama Menolak Unik', setuju: false, kode: 'S-778');
        kerjakanTes($siswa, $pre, 0.5);
        $unit = Meeting::query()->where('urutan', 1)->first()->lessonUnits->firstWhere('tipe', 'pemeriksaan');
        app(AdaptationRecorder::class)->evaluasiDanRekam($siswa, $unit, 0.9);
        $siswa->products()->count(); // relasi ada

        File::deleteDirectory(storage_path('app/ekspor'));

        $this->artisan('geulis:ekspor', ['--oleh' => $this->peneliti->id])->assertSuccessful();

        $ekspor = DataExport::query()->firstOrFail();
        $path = storage_path('app/'.$ekspor->berkas);

        expect($ekspor->dianonimkan)->toBeTrue()
            ->and($ekspor->parameter['lembar'])->toContain('siswa', 'ct_pretest', 'ngain', 'mastery', 'adaptasi', 'motif', 'angket_siswa', 'angket_guru', 'validasi_ahli', 'observasi', 'refleksi')
            ->and(count($ekspor->parameter['lembar']))->toBeGreaterThanOrEqual(10)
            ->and(file_exists($path))->toBeTrue();

        // Bongkar xlsx: cari teks di seluruh sharedStrings & sheet.
        $zip = new ZipArchive;
        $zip->open($path);
        $teks = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $teks .= $zip->getFromIndex($i);
        }
        $zip->close();

        expect($teks)->toContain('S-777')
            ->not->toContain('Reza Pratama Unik')
            ->not->toContain('0099887766')
            ->not->toContain('S-778')
            ->not->toContain('Nama Menolak Unik')
            ->and($teks)->toContain('RULE_PROMOTE' === 'x' ? '' : 'adaptasi');

        actingAs($this->peneliti)->get(route('riset.ekspor.unduh', $ekspor))->assertOk();
        actingAs($this->eksperimen->guru)->get(route('riset.ekspor.unduh', $ekspor))->assertForbidden();

        File::deleteDirectory(storage_path('app/ekspor'));
        File::makeDirectory(storage_path('app/ekspor'));
    });

    it('refuses to export when anonymisation is switched off', function (): void {
        config(['geulis.anonimkan_ekspor' => false]);

        $this->artisan('geulis:ekspor')->assertFailed();

        expect(DataExport::query()->count())->toBe(0);

        Livewire::actingAs($this->peneliti)->test(Ekspor::class)->assertSee('ekspor dinonaktifkan');
    });
});
