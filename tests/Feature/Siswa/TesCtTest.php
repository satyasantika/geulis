<?php

use App\Livewire\Guru\PenilaianUraian;
use App\Livewire\Siswa\Jalur;
use App\Livewire\Siswa\TesCt;
use App\Models\Classroom;
use App\Models\CtScore;
use App\Models\CtTest;
use App\Models\User;
use Database\Seeders\CtTestSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->tes = CtTest::factory()->denganButir()->create(['durasi_menit' => 30]);
    [$this->pgD, $this->pgP, $this->urA, $this->urAl] = $this->tes->items->values();

    $this->kelas = Classroom::factory()->create();
    $this->siswa = User::factory()->siswa()->create();
    $this->siswa->consent()->create(['setuju_data_penelitian' => true, 'disetujui_pada' => now()]);
    $this->kelas->enrollments()->create(['user_id' => $this->siswa->id]);
    $this->siswa->placement()->create(['skor_readiness' => 72, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);
});

it('starts the clock on first open, autosaves answers, and resumes them after a reload', function (): void {
    $komponen = Livewire::actingAs($this->siswa)->test(TesCt::class, ['ctTest' => $this->tes])
        ->assertSee('Sisa waktu')
        ->call('simpanJawaban', $this->pgD->id, 'b')
        ->assertReturned(true)
        ->call('simpanJawaban', $this->urA->id, 'Setengah motif, karena…');

    $skor = CtScore::query()->where('user_id', $this->siswa->id)->firstOrFail();

    expect($skor->dikumpulkan_pada)->toBeNull()
        ->and($this->siswa->ctResponses()->count())->toBe(2)
        ->and($this->siswa->ctResponses()->where('ct_item_id', $this->pgD->id)->first()->skor_otomatis)->toBe(4.0)
        ->and($this->siswa->ctResponses()->where('ct_item_id', $this->urA->id)->first()->skor_otomatis)->toBeNull();

    Livewire::actingAs($this->siswa->fresh())->test(TesCt::class, ['ctTest' => $this->tes])
        ->assertSet("jawaban.{$this->pgD->id}", 'b')
        ->assertSee('Setengah motif');
});

it('submits every answer at once so answers that never synced still count', function (): void {
    Livewire::actingAs($this->siswa)->test(TesCt::class, ['ctTest' => $this->tes])
        ->call('simpanJawaban', $this->pgD->id, 'a') // jawaban lama di server
        ->call('kumpulkan', [
            (string) $this->pgD->id => 'b',           // localStorage menang: benar
            (string) $this->pgP->id => 'x',           // benar
            (string) $this->urA->id => 'uraian A',
            // urAl tidak pernah disentuh
        ])
        ->assertSet('selesai', true)
        ->assertSee('sudah terkumpul');

    $skor = CtScore::query()->where('user_id', $this->siswa->id)->firstOrFail();

    expect($skor->dikumpulkan_pada)->not->toBeNull()
        ->and($skor->skor_d)->toBe(4.0)
        ->and($skor->skor_p)->toBe(4.0)
        ->and($skor->skor_total)->toBe(8.0)
        ->and($skor->persen)->toBe(50.0)
        ->and($skor->dihitung_pada)->toBeNull() // uraian belum dinilai
        ->and($this->siswa->ctResponses()->count())->toBe(4);
});

it('refuses autosave after the time limit and grace period have passed', function (): void {
    Livewire::actingAs($this->siswa)->test(TesCt::class, ['ctTest' => $this->tes]);

    $this->travel(32)->minutes();

    Livewire::actingAs($this->siswa->fresh())->test(TesCt::class, ['ctTest' => $this->tes])
        ->call('simpanJawaban', $this->pgD->id, 'b')
        ->assertReturned(false);

    expect($this->siswa->ctResponses()->count())->toBe(0);
});

it('does not let a submitted test be changed or submitted twice', function (): void {
    $komponen = Livewire::actingAs($this->siswa)->test(TesCt::class, ['ctTest' => $this->tes])
        ->call('kumpulkan', [(string) $this->pgD->id => 'b']);

    $komponen->call('simpanJawaban', $this->pgD->id, 'c')->assertReturned(false);
    $komponen->call('kumpulkan', [(string) $this->pgD->id => 'c']);

    expect($this->siswa->ctResponses()->where('ct_item_id', $this->pgD->id)->first()->jawaban)->toBe('b');
});

it('hides an inactive test and lists an active one on the learning path', function (): void {
    $this->tes->update(['aktif' => false]);
    actingAs($this->siswa)->get(route('siswa.tes-ct', $this->tes))->assertNotFound();

    $this->tes->update(['aktif' => true]);
    Livewire::actingAs($this->siswa)->test(Jalur::class)->assertSee('Mulai tes')->assertSee($this->tes->judul);
});

it('lets the teacher grade essays with the item rubric and completes the score', function (): void {
    Livewire::actingAs($this->siswa)->test(TesCt::class, ['ctTest' => $this->tes])
        ->call('kumpulkan', [(string) $this->pgD->id => 'b', (string) $this->urA->id => 'uraian abstraksi', (string) $this->urAl->id => 'uraian algoritma']);

    $responsA = $this->siswa->ctResponses()->where('ct_item_id', $this->urA->id)->firstOrFail();
    $responsAl = $this->siswa->ctResponses()->where('ct_item_id', $this->urAl->id)->firstOrFail();

    $komponen = Livewire::actingAs($this->kelas->guru)->test(PenilaianUraian::class)
        ->assertSee('uraian abstraksi')
        ->assertSee('lengkap') // deskriptor rubrik
        ->call('nilai', $responsA->id)
        ->assertHasErrors(["skor.{$responsA->id}"])
        ->set("skor.{$responsA->id}", 3)
        ->set("catatan.{$responsA->id}", 'Hampir lengkap.')
        ->call('nilai', $responsA->id)
        ->assertHasNoErrors()
        ->assertDontSee('uraian abstraksi');

    expect($responsA->fresh()->skor_manual)->toBe(3.0)
        ->and($responsA->fresh()->penilai_id)->toBe($this->kelas->guru->id)
        ->and(CtScore::query()->where('user_id', $this->siswa->id)->first()->dihitung_pada)->toBeNull();

    $komponen->set("skor.{$responsAl->id}", 4)->call('nilai', $responsAl->id);

    $skor = CtScore::query()->where('user_id', $this->siswa->id)->firstOrFail();

    expect($skor->dihitung_pada)->not->toBeNull()
        ->and($skor->skor_a)->toBe(3.0)
        ->and($skor->skor_al)->toBe(4.0)
        ->and($skor->persen)->toBe(68.75);
});

it('only shows a teacher the essays of their own students', function (): void {
    Livewire::actingAs($this->siswa)->test(TesCt::class, ['ctTest' => $this->tes])
        ->call('kumpulkan', [(string) $this->urA->id => 'rahasia kelas lain']);

    Livewire::actingAs(User::factory()->guru()->create())->test(PenilaianUraian::class)->assertDontSee('rahasia kelas lain');
});

it('seeds a parallel pretest and posttest with two items per indicator', function (): void {
    $this->seed(CtTestSeeder::class);

    $pre = CtTest::query()->where('jenis', 'pretest')->firstOrFail();

    expect(CtTest::query()->count())->toBe(2)
        ->and($pre->items()->count())->toBe(8)
        ->and($pre->items()->where('indikator', 'Al')->count())->toBe(2)
        ->and($pre->items()->where('tipe', 'uraian')->whereNotNull('rubrik_butir')->count())->toBe(4);
});
