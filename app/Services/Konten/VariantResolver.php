<?php

namespace App\Services\Konten;

use App\Models\ContentVariant;
use App\Models\LessonUnit;
use App\Models\User;
use App\Services\Differentiation\DifferentiationEngine;

/**
 * Memilih varian konten sebuah unit untuk seorang siswa: ambil level & modus
 * dari enrollment aktif, lalu serahkan pemilihannya ke
 * DifferentiationEngine::pilihVarian() (murni, sudah diuji).
 * Hanya varian berstatus `siap` yang disajikan ke siswa.
 */
final class VariantResolver
{
    public function __construct(private readonly DifferentiationEngine $engine) {}

    public function untuk(User $siswa, LessonUnit $unit): ?ContentVariant
    {
        $enrollment = $siswa->enrollmentAktif();
        $level = $enrollment?->level_kini ?? $siswa->placement?->level_awal ?? 'L2';
        $modus = $enrollment?->modus_kini ?? $siswa->placement?->modus ?? 'campuran';

        return $this->pilih($unit, $level, $modus);
    }

    public function pilih(LessonUnit $unit, string $level, string $modus): ?ContentVariant
    {
        $kandidat = $unit->contentVariants()->where('status', 'siap')->get();

        $terpilih = $this->engine->pilihVarian(
            $kandidat->map(fn (ContentVariant $v) => ['id' => $v->getKey(), 'level' => $v->level, 'modus' => $v->modus])->all(),
            $level,
            $modus,
        );

        return $terpilih === null ? null : $kandidat->firstWhere('id', $terpilih['id']);
    }
}
