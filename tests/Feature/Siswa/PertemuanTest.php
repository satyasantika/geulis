<?php

use App\Livewire\Siswa\Jalur;
use App\Livewire\Siswa\Unit;
use App\Models\Activity;
use App\Models\AdaptationLog;
use App\Models\Classroom;
use App\Models\ContentVariant;
use App\Models\CulturalAsset;
use App\Models\LessonUnit;
use App\Models\Meeting;
use App\Models\Reflection;
use App\Models\User;
use App\Services\Konten\KemajuanSiswa;
use Database\Seeders\MeetingSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->seed(MeetingSeeder::class);
    Meeting::query()->whereIn('urutan', [1, 2])->update(['terbit' => true]);
    $this->p1 = Meeting::query()->where('urutan', 1)->firstOrFail();
    $this->p2 = Meeting::query()->where('urutan', 2)->firstOrFail();

    $this->siswa = User::factory()->siswa()->create();
    $this->siswa->consent()->create(['setuju_data_penelitian' => true, 'disetujui_pada' => now()]);
    Classroom::factory()->create()->enrollments()->create(['user_id' => $this->siswa->id, 'level_kini' => 'L2', 'modus_kini' => 'visual']);
    $this->siswa->placement()->create(['skor_readiness' => 72, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);
});

function selesaikanPertemuan(User $siswa, Meeting $m): void
{
    $kemajuan = app(KemajuanSiswa::class);
    foreach ($m->lessonUnits as $u) {
        $kemajuan->tandaiSelesai($siswa, $u);
    }
}

describe('pengunci pertemuan', function (): void {
    it('opens meeting 1 and locks meeting 2 until meeting 1 is finished', function (): void {
        Livewire::actingAs($this->siswa)->test(Jalur::class)
            ->assertSee($this->p1->judul)
            ->assertSee('Terbuka setelah pertemuan sebelumnya selesai.');

        actingAs($this->siswa)->get(route('siswa.pertemuan', $this->p2))->assertRedirect(route('siswa.jalur'));
        actingAs($this->siswa)->get(route('siswa.pertemuan', $this->p1))->assertOk()->assertSee('Jangkar Budaya');

        selesaikanPertemuan($this->siswa, $this->p1);

        actingAs($this->siswa)->get(route('siswa.pertemuan', $this->p2))->assertOk();
        Livewire::actingAs($this->siswa)->test(Jalur::class)->assertSee('Selesai');
    });

    it('keeps an unpublished meeting closed even after the previous one', function (): void {
        selesaikanPertemuan($this->siswa, $this->p1);
        selesaikanPertemuan($this->siswa, $this->p2);
        $p3 = Meeting::query()->where('urutan', 3)->firstOrFail();

        actingAs($this->siswa)->get(route('siswa.pertemuan', $p3))->assertRedirect(route('siswa.jalur'));
    });

    it('locks a unit until the previous unit in the meeting is done', function (): void {
        [$u1, $u2] = $this->p1->lessonUnits->take(2)->values();

        actingAs($this->siswa)->get(route('siswa.unit', $u2))->assertRedirect(route('siswa.pertemuan', $this->p1));

        Livewire::actingAs($this->siswa)->test(Unit::class, ['lessonUnit' => $u1])->call('selesai')->assertRedirect(route('siswa.pertemuan', $this->p1));

        actingAs($this->siswa)->get(route('siswa.unit', $u2))->assertOk();
    });
});

describe('varian, atribusi, geogebra', function (): void {
    it('shows the variant for the student level with the cultural attribution and a self-hosted GeoGebra', function (): void {
        $jangkar = $this->p1->lessonUnits->firstWhere('tipe', 'jangkar');
        $aset = CulturalAsset::factory()->create(['nama_motif' => 'Kisi Rajapolah', 'perajin_sumber' => 'Pak Dadang', 'lokasi' => 'Rajapolah']);
        ContentVariant::factory()->for($jangkar)->untuk('L2', 'visual')->create(['judul' => 'Kisi yang berulang', 'cultural_asset_id' => $aset->id, 'konfigurasi_geogebra' => ['app' => 'geometry', 'terpandu' => true, 'perintah' => "A=(1,2)\nB=(3,2)"]]);
        ContentVariant::factory()->for($jangkar)->untuk('L1', '*')->create(['judul' => 'Versi terbimbing']);

        $respons = actingAs($this->siswa)->get(route('siswa.unit', $jangkar))
            ->assertOk()
            ->assertSee('Kisi yang berulang')
            ->assertDontSee('Versi terbimbing')
            ->assertSee('Pak Dadang')
            ->assertSee('izin diperoleh')
            ->assertSee('geogebra/deployggb.js')
            ->assertSee('HTML5/5.0/web3d')
            ->assertSee('A=(1,2)');

        expect($respons->getContent())->not->toContain('geogebra.org');
    });
});

describe('pemeriksaan penguasaan (S-08)', function (): void {
    function bukaSampai(User $siswa, Meeting $m, string $tipe): LessonUnit
    {
        $kemajuan = app(KemajuanSiswa::class);
        foreach ($m->lessonUnits as $u) {
            if ($u->tipe === $tipe) {
                return $u;
            }
            $kemajuan->tandaiSelesai($siswa, $u);
        }
        throw new RuntimeException('unit tidak ditemukan');
    }

    it('scores a mastery check, records one adaptation log, and marks the unit done on reinforce', function (): void {
        $pemeriksaan = bukaSampai($this->siswa, $this->p1, 'pemeriksaan');
        Activity::factory()->for($pemeriksaan)->kuis(4)->create(['judul' => 'Pemeriksaan']);

        $komponen = Livewire::actingAs($this->siswa)->test(Unit::class, ['lessonUnit' => $pemeriksaan])
            ->set('jawaban', [0 => 0, 1 => 0, 2 => 1, 3 => 1]) // 2 dari 4 → s = 0,5 → M = 0,4·0,72 + 0,6·0,5 = 0,588
            ->call('kirimKuis')
            ->assertHasNoErrors()
            ->assertSee('Sudah lumayan')
            ->assertSee('RULE_REINFORCE')
            ->assertSee('2 dari 4 benar')
            ->assertSee('refleksi sumbu-x #3');

        expect(AdaptationLog::query()->count())->toBe(1)
            ->and(AdaptationLog::query()->first()->kode_aturan)->toBe('RULE_REINFORCE')
            ->and(app(KemajuanSiswa::class)->unitSelesai($this->siswa, $pemeriksaan))->toBeTrue()
            ->and($this->siswa->activityAttempts()->first()->skor)->toBe(50.0)
            ->and($this->siswa->activityAttempts()->first()->selesai_pada)->not->toBeNull();
    });

    it('offers a guided retry on remediation and serves the lower-level variant', function (): void {
        $pemeriksaan = bukaSampai($this->siswa, $this->p1, 'pemeriksaan');
        Activity::factory()->for($pemeriksaan)->kuis(4)->create();
        ContentVariant::factory()->for($pemeriksaan)->untuk('L1', '*')->create(['judul' => 'Versi terbimbing']);
        ContentVariant::factory()->for($pemeriksaan)->untuk('L2', '*')->create(['judul' => 'Versi biasa']);

        $komponen = Livewire::actingAs($this->siswa)->test(Unit::class, ['lessonUnit' => $pemeriksaan])
            ->assertSee('Versi biasa')
            ->set('jawaban', [0 => 1, 1 => 1, 2 => 1, 3 => 1])
            ->call('kirimKuis')
            ->assertSee('pelan-pelan')
            ->assertSee('RULE_REMEDIATE')
            ->assertSee('1 dari 2');

        expect(app(KemajuanSiswa::class)->unitSelesai($this->siswa, $pemeriksaan))->toBeFalse()
            ->and($this->siswa->enrollmentAktif()->level_kini)->toBe('L1');

        $komponen->call('cobaLagi')->assertSee('Versi terbimbing')->assertDontSee('Versi biasa');

        expect($this->siswa->activityAttempts()->count())->toBe(2)
            ->and($this->siswa->activityAttempts()->latest('id')->first()->percobaan_ke)->toBe(2);
    });

    it('does not trigger adaptation for ordinary practice quizzes', function (): void {
        $latihan = bukaSampai($this->siswa, $this->p1, 'latihan');
        Activity::factory()->for($latihan)->kuis(2)->create(['judul' => 'Latihan']);

        Livewire::actingAs($this->siswa)->test(Unit::class, ['lessonUnit' => $latihan])
            ->set('jawaban', [0 => 0, 1 => 0])
            ->call('kirimKuis')
            ->assertSee('2 dari 2 benar')
            ->assertDontSee('Kode aturan');

        expect(AdaptationLog::query()->count())->toBe(0)
            ->and(app(KemajuanSiswa::class)->unitSelesai($this->siswa, $latihan))->toBeTrue();
    });

    it('requires every item to be answered', function (): void {
        $pemeriksaan = bukaSampai($this->siswa, $this->p1, 'pemeriksaan');
        Activity::factory()->for($pemeriksaan)->kuis(3)->create();

        Livewire::actingAs($this->siswa)->test(Unit::class, ['lessonUnit' => $pemeriksaan])
            ->set('jawaban', [0 => 0])
            ->call('kirimKuis')
            ->assertHasErrors(['jawaban']);

        expect(AdaptationLog::query()->count())->toBe(0);
    });
});

describe('refleksi', function (): void {
    it('stores both answers and finishes the meeting', function (): void {
        $refleksi = bukaSampai($this->siswa, $this->p1, 'refleksi');

        Livewire::actingAs($this->siswa)->test(Unit::class, ['lessonUnit' => $refleksi])
            ->call('kirimRefleksi')->assertHasErrors(['refleksi'])
            ->set('refleksi', [0 => 'Bagian cermin.', 1 => 'Lipat dulu.'])
            ->call('kirimRefleksi')
            ->assertRedirect(route('siswa.pertemuan', $this->p1));

        expect($this->siswa->fresh()->unitProgress()->where('status', 'selesai')->count())->toBe(7)
            ->and(Reflection::query()->where('user_id', $this->siswa->id)->count())->toBe(2)
            ->and(app(KemajuanSiswa::class)->pertemuanTerbuka($this->siswa->fresh(), $this->p2))->toBeTrue();
    });
});
