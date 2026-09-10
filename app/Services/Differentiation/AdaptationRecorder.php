<?php

namespace App\Services\Differentiation;

use App\Models\AdaptationLog;
use App\Models\LessonUnit;
use App\Models\MasteryState;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Lapisan penyimpanan adaptasi berkelanjutan. Mesinnya (DifferentiationEngine)
 * tidak tahu apa-apa soal basis data; kelas inilah yang:
 *   1. mengambil status penguasaan siswa pada unit (atau membuatnya dari R/100),
 *   2. memanggil evaluasi(),
 *   3. menulis mastery_states (diperbarui) dan adaptation_logs (HANYA ditambah),
 *   4. memperbarui level_kini pada enrollment.
 */
final class AdaptationRecorder
{
    public function __construct(private readonly DifferentiationEngine $engine) {}

    /**
     * @param  array{durasi_detik?: int, median_durasi_kelas?: int, butir_salah?: array<int, mixed>}  $sinyal
     */
    public function evaluasiDanRekam(User $siswa, LessonUnit $unit, float $skorPemeriksaan, array $sinyal = []): AdaptationDecision
    {
        return DB::transaction(function () use ($siswa, $unit, $skorPemeriksaan, $sinyal): AdaptationDecision {
            $status = $this->statusPenguasaan($siswa, $unit);

            $keputusan = $this->engine->evaluasi(
                mLama: (float) $status->nilai_m,
                skorPemeriksaan: $skorPemeriksaan,
                levelKini: $status->level_saat_itu,
                iterasiRemedial: (int) $status->iterasi_remedial,
                sinyal: $sinyal,
            );

            $this->rekam($siswa, $unit, $status, $keputusan);

            return $keputusan;
        });
    }

    /**
     * Status penguasaan siswa pada unit; bila belum ada, M awal = R/100 dan
     * level = level_kini pada enrollment (cetak biru §3.3).
     */
    public function statusPenguasaan(User $siswa, LessonUnit $unit): MasteryState
    {
        $status = MasteryState::query()->firstOrNew([
            'user_id' => $siswa->getKey(),
            'lesson_unit_id' => $unit->getKey(),
        ]);

        if (! $status->exists) {
            $placement = $siswa->placement;
            $enrollment = $siswa->enrollments()->where('status', 'aktif')->first();

            $status->nilai_m = $placement ? round($placement->skor_readiness / 100, 3) : 0.5;
            $status->level_saat_itu = $enrollment?->level_kini ?? $placement?->level_awal ?? 'L2';
            $status->iterasi_remedial = 0;
            $status->perlu_pendampingan = false;
        }

        return $status;
    }

    public function rekam(User $siswa, LessonUnit $unit, MasteryState $status, AdaptationDecision $keputusan): AdaptationLog
    {
        $status->fill([
            'nilai_m' => $keputusan->mSesudah,
            'level_saat_itu' => $keputusan->levelSesudah,
            'iterasi_remedial' => $keputusan->iterasiRemedial,
            'perlu_pendampingan' => $keputusan->perluPendampingan,
        ])->save();

        if ($keputusan->levelSesudah !== $keputusan->levelSebelum) {
            $siswa->enrollments()->where('status', 'aktif')->update(['level_kini' => $keputusan->levelSesudah]);
        }

        // Selalu create(), tidak pernah update — inilah jejak empirisnya.
        return AdaptationLog::query()->create([
            'user_id' => $siswa->getKey(),
            'lesson_unit_id' => $unit->getKey(),
            ...$keputusan->toLogArray(),
        ]);
    }
}
