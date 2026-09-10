<?php

use App\Livewire\Siswa\MotifBuilder;
use App\Models\Activity;
use App\Models\Classroom;
use App\Models\Meeting;
use App\Models\MotifSubmission;
use App\Models\User;
use App\Services\Konten\KemajuanSiswa;
use App\Services\Motif\PenilaiMotif;
use Database\Seeders\MeetingSeeder;
use Database\Seeders\MotifSasaranSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->seed([MeetingSeeder::class, MotifSasaranSeeder::class]);
    Meeting::query()->update(['terbit' => true]);
    $this->p3 = Meeting::query()->where('urutan', 3)->firstOrFail();
    $this->rozet = Activity::query()->where('tipe', 'motif_builder')->whereHas('lessonUnit', fn ($q) => $q->where('meeting_id', $this->p3->id))->firstOrFail();

    $this->siswa = User::factory()->siswa()->create();
    $this->siswa->consent()->create(['setuju_data_penelitian' => true, 'disetujui_pada' => now()]);
    Classroom::factory()->create()->enrollments()->create(['user_id' => $this->siswa->id, 'level_kini' => 'L2', 'modus_kini' => 'visual']);
    $this->siswa->placement()->create(['skor_readiness' => 72, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'payung_geulis', 'penjelasan' => []]);

    // Buka sampai unit motif pertemuan 3: selesaikan P1, P2, dan unit 1–5 P3.
    $kemajuan = app(KemajuanSiswa::class);
    foreach (Meeting::query()->whereIn('urutan', [1, 2])->get() as $m) {
        foreach ($m->lessonUnits as $u) {
            $kemajuan->tandaiSelesai($this->siswa, $u);
        }
    }
    foreach ($this->p3->lessonUnits->where('urutan', '<', 6) as $u) {
        $kemajuan->tandaiSelesai($this->siswa, $u);
    }
});

$rotasiBerulang = [
    ['op' => 'MOTIF_DASAR'],
    ['op' => 'ULANGI', 'n' => 8, 'badan' => [['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 45]]],
];

it('seeds a target motif for each of the five meetings', function (): void {
    expect(Activity::query()->where('tipe', 'motif_builder')->count())->toBe(5);
});

it('passes a correct sequence with similarity ≥ 90 and marks the unit done', function () use ($rotasiBerulang): void {
    Livewire::actingAs($this->siswa)
        ->test(MotifBuilder::class, ['activity' => $this->rozet])
        ->call('kirim', $rotasiBerulang, [0], '<svg xmlns="http://www.w3.org/2000/svg"></svg>')
        ->assertSet('galat', null)
        ->assertSee('Lolos')
        ->assertSee('Efisiensi 100%');

    $kiriman = MotifSubmission::query()->firstOrFail();

    expect((float) $kiriman->skor_kemiripan)->toBeGreaterThanOrEqual(99.0)
        ->and($kiriman->lolos)->toBeTrue()
        ->and($kiriman->langkah_siswa)->toBe(2)
        ->and($kiriman->langkah_minimum)->toBe(2)
        ->and((float) $kiriman->efisiensi)->toBe(100.0)
        ->and($kiriman->motif_dasar_ditandai)->toBe([0])
        ->and($kiriman->cuplikan_svg)->toStartWith('<svg')
        ->and(app(KemajuanSiswa::class)->unitSelesai($this->siswa, $this->rozet->lessonUnit))->toBeTrue();
});

it('still passes a longer but correct sequence, only with lower efficiency', function (): void {
    $delapanRotasi = [['op' => 'MOTIF_DASAR']];
    foreach (range(1, 8) as $i) {
        $delapanRotasi[] = ['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 45];
    }

    $kiriman = app(PenilaiMotif::class)->nilai($this->siswa, $this->rozet, $delapanRotasi, [0]);

    expect($kiriman->lolos)->toBeTrue()
        ->and($kiriman->langkah_siswa)->toBe(8)
        ->and((float) $kiriman->efisiensi)->toBe(25.0);
});

it('fails a wrong sequence and lets the student retry with the attempt number increasing', function () use ($rotasiBerulang): void {
    $komponen = Livewire::actingAs($this->siswa)
        ->test(MotifBuilder::class, ['activity' => $this->rozet])
        ->call('kirim', [['op' => 'MOTIF_DASAR'], ['op' => 'ULANGI', 'n' => 4, 'badan' => [['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 90]]]], [0], null)
        ->assertSee('Belum sampai');

    expect(MotifSubmission::query()->first()->lolos)->toBeFalse()
        ->and(app(KemajuanSiswa::class)->unitSelesai($this->siswa, $this->rozet->lessonUnit))->toBeFalse();

    $komponen->call('kirim', $rotasiBerulang, [0, 1], null)->assertSee('Lolos');

    expect(MotifSubmission::query()->count())->toBe(2)
        ->and(MotifSubmission::query()->latest('id')->first()->percobaan_ke)->toBe(2);
});

it('rejects a malformed command without saving anything', function (): void {
    Livewire::actingAs($this->siswa)
        ->test(MotifBuilder::class, ['activity' => $this->rozet])
        ->call('kirim', [['op' => 'MOTIF_DASAR'], ['op' => 'ROTASI', 'pusat' => [0], 'sudut' => 'x']], [], null)
        ->assertSet('galat', 'pusat rotasi harus berupa pasangan angka.');

    expect(MotifSubmission::query()->count())->toBe(0);
});

it('lets the student move on after three saved attempts even without passing', function (): void {
    $salah = [['op' => 'MOTIF_DASAR'], ['op' => 'TRANSLASI', 'vektor' => [1, 1]]];
    $komponen = Livewire::actingAs($this->siswa)->test(MotifBuilder::class, ['activity' => $this->rozet]);

    $komponen->call('lanjutkan');
    expect(app(KemajuanSiswa::class)->unitSelesai($this->siswa, $this->rozet->lessonUnit))->toBeFalse();

    foreach (range(1, 3) as $i) {
        $komponen->call('kirim', $salah, [], null);
    }

    $komponen->assertSee('3 percobaan tersimpan')->call('lanjutkan')->assertRedirect(route('siswa.pertemuan', $this->p3));

    expect(app(KemajuanSiswa::class)->unitSelesai($this->siswa->fresh(), $this->rozet->lessonUnit))->toBeTrue();
});

it('derives all four CT indicators from a submission', function () use ($rotasiBerulang): void {
    $kiriman = app(PenilaiMotif::class)->nilai($this->siswa, $this->rozet, $rotasiBerulang, [3]);

    $bukti = app(PenilaiMotif::class)->buktiCT($kiriman);

    expect($bukti['dekomposisi'])->toBe(['ditandai' => 1, 'diharapkan' => 1, 'tepat' => 1])
        ->and($bukti['pengenalan_pola'])->toBe(['n_pengulangan' => 8, 'benar' => true])
        ->and($bukti['abstraksi']['proporsi'])->toBe(1.0)
        ->and($bukti['algoritma'])->toBe(['jumlah_langkah' => 3, 'memakai_perulangan' => true]);
});

it('keeps the builder behind the unit lock and renders it self-contained', function (): void {
    $lain = User::factory()->siswa()->create();
    $lain->consent()->create(['setuju_data_penelitian' => true, 'disetujui_pada' => now()]);
    $lain->placement()->create(['skor_readiness' => 72, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);

    actingAs($lain)->get(route('siswa.motif', $this->rozet))->assertRedirect(route('siswa.pertemuan', $this->p3));

    actingAs($this->siswa)->get(route('siswa.motif', $this->rozet))
        ->assertOk()
        ->assertSee('Tandai motif dasarnya')
        ->assertSee('motifBuilder(', escape: false)
        ->assertSee('livewireScriptConfig', escape: false)
        ->assertDontSee('cdn.');
});
