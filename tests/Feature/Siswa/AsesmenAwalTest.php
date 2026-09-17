<?php

use App\Livewire\Siswa\AsesmenAwal;
use App\Models\Classroom;
use App\Models\ReadinessItem;
use App\Models\User;
use Database\Seeders\ReadinessItemSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->seed(ReadinessItemSeeder::class);
    $this->siswa = User::factory()->siswa()->create();
    $this->siswa->consent()->create(['setuju_data_penelitian' => true, 'disetujui_pada' => now()]);
    Classroom::factory()->create()->enrollments()->create(['user_id' => $this->siswa->id]);
});

it('walks a student through readiness, profile, and interest and places them', function (): void {
    $butir = ReadinessItem::query()->orderBy('urutan')->get();
    $komponen = Livewire::actingAs($this->siswa)->test(AsesmenAwal::class)->assertSet('tahap', 'kesiapan');

    foreach ($butir as $i => $b) {
        // 12 benar dari 15 → R = 80 → L3
        $komponen->set('jawaban', $i < 12 ? $b->kunci : ($b->kunci + 1) % 4)->call('jawabKesiapan')->assertHasNoErrors();
    }

    $komponen->assertSet('tahap', 'profil');

    foreach (config('angket.profil') as $i => $b) {
        $komponen->set("jawabanProfil.{$i}", $b['modus'] === 'simbolik' ? 4 : 1);
        if (($i + 1) % AsesmenAwal::BUTIR_PER_HALAMAN === 0) {
            $komponen->call('halamanBerikutnya')->assertHasNoErrors();
        }
    }

    $komponen->assertSet('tahap', 'minat');

    foreach (config('angket.minat') as $i => $b) {
        $komponen->set("jawabanMinat.{$i}", $b['artefak'] === 'batik' ? 4 : 2);
    }
    $komponen->call('halamanBerikutnya')->assertSet('tahap', 'selesai')->call('selesai')->assertRedirect(route('siswa.jalur'));

    $placement = $this->siswa->fresh()->placement;

    expect($placement->skor_readiness)->toBe(80)
        ->and($placement->level_awal)->toBe('L3')
        ->and($placement->modus)->toBe('simbolik')
        ->and($placement->artefak_utama)->toBe('batik')
        ->and($this->siswa->readinessResponses()->count())->toBe(15);

    actingAs($this->siswa->fresh())->get('/belajar')->assertOk()->assertSee('Mahir')->assertSee('Simbolik-Analitis');
});

it('resumes from the next unanswered readiness item after a dropped connection', function (): void {
    $butir = ReadinessItem::query()->orderBy('urutan')->get();
    foreach ($butir->take(4) as $b) {
        $this->siswa->readinessResponses()->create(['readiness_item_id' => $b->id, 'jawaban' => 0, 'benar' => false]);
    }

    Livewire::actingAs($this->siswa)->test(AsesmenAwal::class)
        ->assertSet('tahap', 'kesiapan')
        ->assertSet('butirId', $butir[4]->id)
        ->assertSee('Butir 5 dari 15');
});

it('keeps questionnaire drafts so the student does not start over', function (): void {
    foreach (ReadinessItem::all() as $b) {
        $this->siswa->readinessResponses()->create(['readiness_item_id' => $b->id, 'jawaban' => $b->kunci, 'benar' => true]);
    }

    Livewire::actingAs($this->siswa)->test(AsesmenAwal::class)
        ->assertSet('tahap', 'profil')
        ->set('jawabanProfil.0', 3)
        ->set('jawabanProfil.1', 2);

    expect($this->siswa->fresh()->learningProfile->jawaban_mentah['profil'])->toBe([3, 2]);

    Livewire::actingAs($this->siswa->fresh())->test(AsesmenAwal::class)->assertSet('jawabanProfil', [3, 2]);
});

it('requires an answer before moving on', function (): void {
    Livewire::actingAs($this->siswa)->test(AsesmenAwal::class)
        ->call('jawabKesiapan')
        ->assertHasErrors(['jawaban']);

    expect($this->siswa->readinessResponses()->count())->toBe(0);
});

it('blocks moving to the next questionnaire page until every item on it is answered', function (): void {
    foreach (ReadinessItem::all() as $b) {
        $this->siswa->readinessResponses()->create(['readiness_item_id' => $b->id, 'jawaban' => $b->kunci, 'benar' => true]);
    }

    Livewire::actingAs($this->siswa)->test(AsesmenAwal::class)
        ->assertSet('tahap', 'profil')
        ->set('jawabanProfil.0', 3)
        ->set('jawabanProfil.1', 2)
        // butir index 2, 3, 4 (halaman pertama = 5 butir) sengaja dibiarkan kosong
        ->call('halamanBerikutnya')
        ->assertHasErrors(['angket'])
        ->assertSet('halaman', 0)
        ->assertSet('tahap', 'profil');
});

it('sends an already-placed student straight to the learning path', function (): void {
    $this->siswa->placement()->create(['skor_readiness' => 70, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);

    Livewire::actingAs($this->siswa)->test(AsesmenAwal::class)->assertRedirect(route('siswa.jalur'));
    actingAs($this->siswa)->get('/belajar')->assertOk()->assertSee('Berkembang');
});

it('shows the assessment call to action before placement', function (): void {
    actingAs($this->siswa)->get('/belajar')->assertOk()->assertSee('Mulai asesmen awal');
});
