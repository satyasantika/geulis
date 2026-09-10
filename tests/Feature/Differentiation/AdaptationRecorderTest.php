<?php

use App\Models\AdaptationLog;
use App\Models\Classroom;
use App\Models\LessonUnit;
use App\Models\Meeting;
use App\Models\User;
use App\Services\Differentiation\AdaptationDecision;
use App\Services\Differentiation\AdaptationRecorder;
use App\Services\Differentiation\DifferentiationEngine;
use Database\Seeders\MeetingSeeder;

beforeEach(function (): void {
    $this->seed(MeetingSeeder::class);
    $this->unit = LessonUnit::query()->where('tipe', 'pemeriksaan')->firstOrFail();
    $this->kelas = Classroom::factory()->create();
    $this->siswa = User::factory()->siswa()->create();
    $this->kelas->enrollments()->create(['user_id' => $this->siswa->id, 'level_kini' => 'L2']);
    $this->siswa->placement()->create(['skor_readiness' => 72, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);
});

it('starts mastery from R/100 and writes one log row per decision', function (): void {
    $recorder = app(AdaptationRecorder::class);

    $d1 = $recorder->evaluasiDanRekam($this->siswa, $this->unit, 0.40);
    $d2 = $recorder->evaluasiDanRekam($this->siswa, $this->unit, 0.20);

    expect($d1->kodeAturan)->toBe(AdaptationDecision::REINFORCE)
        ->and($d1->mSebelum)->toBe(0.72)
        ->and($d1->mSesudah)->toBe(0.528)
        ->and($d2->kodeAturan)->toBe(AdaptationDecision::REMEDIATE)
        ->and($d2->mSebelum)->toBe(0.528)
        ->and($d2->levelSesudah)->toBe('L1');

    expect(AdaptationLog::query()->count())->toBe(2)
        ->and(AdaptationLog::query()->pluck('kode_aturan')->all())->toBe([AdaptationDecision::REINFORCE, AdaptationDecision::REMEDIATE]);

    $status = $this->siswa->masteryStates()->firstOrFail();

    expect((float) $status->nilai_m)->toBe(0.331)
        ->and($status->level_saat_itu)->toBe('L1')
        ->and($status->iterasi_remedial)->toBe(1)
        ->and($this->siswa->enrollments()->first()->level_kini)->toBe('L1');
});

it('escalates after the remedial limit and flags the student for the teacher', function (): void {
    $recorder = app(AdaptationRecorder::class);

    foreach ([0.1, 0.1, 0.1] as $s) {
        $d = $recorder->evaluasiDanRekam($this->siswa, $this->unit, $s);
    }

    expect($d->kodeAturan)->toBe(AdaptationDecision::ESCALATE)
        ->and($d->perluPendampingan)->toBeTrue()
        ->and($this->siswa->masteryStates()->first()->perlu_pendampingan)->toBeTrue()
        ->and(AdaptationLog::query()->count())->toBe(3);
});

it('never updates or deletes an adaptation log row', function (): void {
    $log = app(AdaptationRecorder::class)->evaluasiDanRekam($this->siswa, $this->unit, 0.9);
    $baris = AdaptationLog::query()->firstOrFail();

    expect(fn () => $baris->update(['keputusan' => 'diubah']))->toThrow(LogicException::class)
        ->and(fn () => $baris->delete())->toThrow(LogicException::class)
        ->and(AdaptationLog::query()->count())->toBe(1)
        ->and(AdaptationLog::query()->first()->keputusan)->not->toBe('diubah');
});

it('records the guess guard without changing mastery', function (): void {
    $d = app(AdaptationRecorder::class)->evaluasiDanRekam($this->siswa, $this->unit, 0.2, [
        'durasi_detik' => 10, 'median_durasi_kelas' => 300,
    ]);

    expect($d->kodeAturan)->toBe(AdaptationDecision::GUESS_GUARD)
        ->and((float) $this->siswa->masteryStates()->first()->nilai_m)->toBe(0.72)
        ->and(AdaptationLog::query()->first()->konteks['sinyal']['durasi_detik'])->toBe(10);
});

it('resolves the engine from the container with the research parameters', function (): void {
    expect(app(DifferentiationEngine::class))
        ->toBe(app(DifferentiationEngine::class));

    expect(Meeting::query()->count())->toBe(5);
});
