<?php

namespace App\Services\Guru;

use App\Models\LessonUnit;
use App\Models\TeacherOverride;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Override level oleh guru (cetak biru §3.3 batas pengaman): mengubah
 * level_kini pada enrollment dan level_saat_itu pada unit yang disebut,
 * lalu mencatat ke teacher_overrides beserta alasan. Tidak menyentuh
 * adaptation_logs — itu jejak mesin, ini jejak guru.
 */
final class PengesampinganGuru
{
    public function jalankan(User $guru, User $siswa, string $levelBaru, string $alasan, ?LessonUnit $unit = null): TeacherOverride
    {
        if (! in_array($levelBaru, ['L1', 'L2', 'L3'], true)) {
            throw new InvalidArgumentException('Level harus L1, L2, atau L3.');
        }
        if (mb_strlen(trim($alasan)) < 10) {
            throw new InvalidArgumentException('Tuliskan alasan yang jelas (minimal 10 karakter).');
        }

        $enrollment = $siswa->enrollments()->where('status', 'aktif')->first();
        if ($enrollment === null || $enrollment->classroom->guru_id !== $guru->getKey()) {
            throw new InvalidArgumentException('Siswa ini tidak ada di kelas yang Anda ampu.');
        }

        return DB::transaction(function () use ($guru, $siswa, $levelBaru, $alasan, $unit, $enrollment): TeacherOverride {
            $levelLama = $enrollment->level_kini;

            $enrollment->update(['level_kini' => $levelBaru]);

            if ($unit !== null) {
                $siswa->masteryStates()->where('lesson_unit_id', $unit->getKey())->update(['level_saat_itu' => $levelBaru]);
            }

            return TeacherOverride::query()->create([
                'guru_id' => $guru->getKey(),
                'siswa_id' => $siswa->getKey(),
                'lesson_unit_id' => $unit?->getKey(),
                'level_lama' => $levelLama,
                'level_baru' => $levelBaru,
                'alasan' => trim($alasan),
            ]);
        });
    }
}
