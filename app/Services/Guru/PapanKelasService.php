<?php

namespace App\Services\Guru;

use App\Models\Classroom;
use App\Models\LessonUnit;
use App\Models\MasteryState;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Data papan kelas G-01: peta panas M per unit pemeriksaan, daftar perlu
 * pendampingan, siswa belum mulai, dan pola unit yang banyak tersendat.
 * Dirancang untuk satu pertanyaan: siapa yang perlu didatangi hari ini.
 */
final class PapanKelasService
{
    /**
     * @return array{
     *   siswa: Collection<int, User>,
     *   unit: Collection<int, LessonUnit>,
     *   m: array<int, array<int, float|null>>,
     *   level: array<int, string>,
     *   pendampingan: Collection<int, MasteryState>,
     *   belum_mulai: Collection<int, User>,
     *   pola: Collection<int, array{unit: LessonUnit, jumlah: int}>,
     *   aktif: int
     * }
     */
    public function data(Classroom $kelas): array
    {
        $siswa = $kelas->siswa()->wherePivot('status', 'aktif')->with('placement')->get();
        $idSiswa = $siswa->pluck('id');

        $unit = LessonUnit::query()->with('meeting')->where('tipe', 'pemeriksaan')
            ->whereHas('meeting', fn ($q) => $q->where('terbit', true))
            ->get()->sortBy(fn (LessonUnit $u) => $u->meeting->urutan)->values();

        $status = MasteryState::query()->whereIn('user_id', $idSiswa)->whereIn('lesson_unit_id', $unit->pluck('id'))->get();

        $m = [];
        foreach ($status as $s) {
            $m[$s->user_id][$s->lesson_unit_id] = (float) $s->nilai_m;
        }

        $level = $siswa->mapWithKeys(fn (User $u) => [$u->id => $u->pivot->level_kini])->all();

        $pendampingan = MasteryState::query()->with(['user', 'lessonUnit.meeting'])
            ->whereIn('user_id', $idSiswa)->where('perlu_pendampingan', true)
            ->orderByDesc('iterasi_remedial')->get();

        $belumMulai = $siswa->filter(fn (User $u) => $u->placement === null)->values();

        $pola = $status->where('iterasi_remedial', '>=', 1)->groupBy('lesson_unit_id')
            ->map(fn ($g, $idUnit) => ['unit' => $unit->firstWhere('id', $idUnit), 'jumlah' => $g->count()])
            ->filter(fn ($p) => $siswa->count() > 0 && $p['jumlah'] / $siswa->count() >= 0.3 && $p['jumlah'] >= 3)
            ->values();

        return [
            'siswa' => $siswa,
            'unit' => $unit,
            'm' => $m,
            'level' => $level,
            'pendampingan' => $pendampingan,
            'belum_mulai' => $belumMulai,
            'pola' => $pola,
            'aktif' => $siswa->count() - $belumMulai->count(),
        ];
    }

    /** Kelas warna peta panas untuk nilai M (null = belum dikerjakan). */
    public static function warna(?float $m): string
    {
        return match (true) {
            $m === null => 'bg-garis-2 text-tinta-3',
            $m >= 0.80 => 'bg-green-200 text-green-900',
            $m >= 0.65 => 'bg-lime-100 text-lime-900',
            $m >= 0.50 => 'bg-amber-100 text-amber-900',
            default => 'bg-red-100 text-red-900',
        };
    }
}
