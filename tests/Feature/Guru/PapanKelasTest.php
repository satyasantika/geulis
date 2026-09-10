<?php

use App\Livewire\Guru\PapanKelas;
use App\Livewire\Guru\Pendampingan;
use App\Livewire\Guru\RaporSiswa;
use App\Models\AdaptationLog;
use App\Models\Classroom;
use App\Models\LessonUnit;
use App\Models\Meeting;
use App\Models\TeacherOverride;
use App\Models\User;
use App\Services\Differentiation\AdaptationRecorder;
use Database\Seeders\MeetingSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->seed(MeetingSeeder::class);
    Meeting::query()->whereIn('urutan', [1, 2])->update(['terbit' => true]);
    $this->unit1 = LessonUnit::query()->where('tipe', 'pemeriksaan')->whereHas('meeting', fn ($q) => $q->where('urutan', 1))->firstOrFail();
    $this->unit2 = LessonUnit::query()->where('tipe', 'pemeriksaan')->whereHas('meeting', fn ($q) => $q->where('urutan', 2))->firstOrFail();

    $this->kelas = Classroom::factory()->create();
    $this->guru = $this->kelas->guru;

    $buat = function (string $nama, int $r) {
        $s = User::factory()->siswa()->create(['nama' => $nama]);
        $this->kelas->enrollments()->create(['user_id' => $s->id, 'level_kini' => 'L2']);
        $s->placement()->create(['skor_readiness' => $r, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);

        return $s;
    };
    $this->ahmad = $buat('Ahmad', 90);
    $this->reza = $buat('Reza', 60);
    $this->belum = User::factory()->siswa()->create(['nama' => 'Alya']);
    $this->kelas->enrollments()->create(['user_id' => $this->belum->id]);

    $recorder = app(AdaptationRecorder::class);
    $recorder->evaluasiDanRekam($this->ahmad, $this->unit1, 1.0);   // M 0,96 → promosi L3
    foreach ([0.1, 0.1, 0.1] as $s) {
        $recorder->evaluasiDanRekam($this->reza, $this->unit1, $s);  // remedial ×2 lalu eskalasi
    }
});

it('shows a real heat map, the escalated student, and who has not started', function (): void {
    Livewire::actingAs($this->guru)->test(PapanKelas::class, ['classroom' => $this->kelas])
        ->assertSee('Ahmad')
        ->assertSee('0,96')
        ->assertSee('L3')
        ->assertSee('Reza')
        ->assertSee('2× remedial')
        ->assertSee('Alya')
        ->assertSee('belum mulai asesmen awal')
        ->assertSee('2 aktif');
});

it('saves a teacher override with its reason and changes the current level', function (): void {
    $komponen = Livewire::actingAs($this->guru)->test(PapanKelas::class, ['classroom' => $this->kelas])
        ->call('bukaUbah', $this->reza->id, 'L1')
        ->set('levelBaru', 'L2')
        ->set('alasan', 'singkat')
        ->call('simpanUbah')
        ->assertHasErrors(['alasan']);

    expect(TeacherOverride::query()->count())->toBe(0);

    $komponen->set('alasan', 'Di kelas Reza sebenarnya paham; ia salah membaca soal karena terburu-buru.')
        ->call('simpanUbah')
        ->assertHasNoErrors()
        ->assertSet('ubahSiswaId', null);

    $override = TeacherOverride::query()->firstOrFail();

    expect($override->guru_id)->toBe($this->guru->id)
        ->and($override->siswa_id)->toBe($this->reza->id)
        ->and($override->level_lama)->toBe('L1')
        ->and($override->level_baru)->toBe('L2')
        ->and($override->alasan)->toContain('terburu-buru')
        ->and($this->reza->enrollmentAktif()->level_kini)->toBe('L2')
        ->and(AdaptationLog::query()->where('user_id', $this->reza->id)->count())->toBe(3); // jejak mesin tidak disentuh
});

it('forbids another teacher from opening the board or overriding', function (): void {
    $lain = User::factory()->guru()->create();

    actingAs($lain)->get(route('guru.papan', $this->kelas))->assertForbidden();
    actingAs($lain)->get(route('guru.siswa', $this->reza))->assertForbidden();
});

it('lists escalated students on the mentoring page and lets the teacher clear them', function (): void {
    $status = $this->reza->masteryStates()->firstOrFail();

    Livewire::actingAs($this->guru)->test(Pendampingan::class)
        ->assertSee('Reza')
        ->assertSee('2× remedial')
        ->call('tandaiDitangani', $status->id)
        ->assertDontSee('Reza');

    expect($status->fresh()->perlu_pendampingan)->toBeFalse()
        ->and(AdaptationLog::query()->count())->toBe(4);
});

it('renders the student report with mastery, decision trail, and an override form', function (): void {
    Livewire::actingAs($this->guru)->test(RaporSiswa::class, ['user' => $this->reza])
        ->assertSee('RULE_ESCALATE')
        ->assertSee('RULE_REMEDIATE')
        ->assertSee('perlu pendampingan')
        ->set('formUbah', true)
        ->set('levelBaru', 'L3')
        ->set('alasan', 'Hasil tugas rumahnya menunjukkan penguasaan yang jauh lebih baik.')
        ->call('simpanUbah')
        ->assertHasNoErrors()
        ->assertSee('L1 → L3');

    expect($this->reza->enrollmentAktif()->level_kini)->toBe('L3');
});
