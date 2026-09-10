<?php

namespace App\Services\Konten;

use App\Models\LessonUnit;
use App\Models\LessonUnitProgress;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Lapisan penyimpanan kemajuan siswa per unit dan pengunci pertemuan:
 * pertemuan ke-n terbuka bila `terbit` dan seluruh unit pertemuan ke-(n−1)
 * sudah selesai. Pertemuan 1 terbuka begitu siswa sudah ditempatkan.
 */
final class KemajuanSiswa
{
    public function tandaiSelesai(User $siswa, LessonUnit $unit): LessonUnitProgress
    {
        return LessonUnitProgress::query()->updateOrCreate(
            ['user_id' => $siswa->getKey(), 'lesson_unit_id' => $unit->getKey()],
            ['status' => 'selesai', 'selesai_pada' => now()],
        );
    }

    public function tandaiSedang(User $siswa, LessonUnit $unit): LessonUnitProgress
    {
        return LessonUnitProgress::query()->firstOrCreate(
            ['user_id' => $siswa->getKey(), 'lesson_unit_id' => $unit->getKey()],
            ['status' => 'sedang'],
        );
    }

    public function unitSelesai(User $siswa, LessonUnit $unit): bool
    {
        return $siswa->unitProgress()->where('lesson_unit_id', $unit->getKey())->where('status', 'selesai')->exists();
    }

    public function pertemuanSelesai(User $siswa, Meeting $pertemuan): bool
    {
        $idUnit = $pertemuan->lessonUnits()->pluck('id');
        if ($idUnit->isEmpty()) {
            return false;
        }

        $selesai = $siswa->unitProgress()->whereIn('lesson_unit_id', $idUnit)->where('status', 'selesai')->count();

        return $selesai === $idUnit->count();
    }

    public function pertemuanTerbuka(User $siswa, Meeting $pertemuan): bool
    {
        if (! $pertemuan->terbit || $siswa->placement === null) {
            return false;
        }

        if ($pertemuan->urutan === 1) {
            return true;
        }

        $sebelumnya = Meeting::query()->where('urutan', $pertemuan->urutan - 1)->first();

        return $sebelumnya !== null && $this->pertemuanSelesai($siswa, $sebelumnya);
    }

    /**
     * Unit yang boleh dibuka: unit pertama pertemuan, atau unit yang
     * pendahulunya sudah selesai. Urutan tetap supaya alur tujuh bagian utuh.
     */
    public function unitTerbuka(User $siswa, LessonUnit $unit): bool
    {
        if (! $this->pertemuanTerbuka($siswa, $unit->meeting)) {
            return false;
        }

        if ($unit->urutan === 1) {
            return true;
        }

        $sebelum = $unit->meeting->lessonUnits()->where('urutan', $unit->urutan - 1)->first();

        return $sebelum !== null && $this->unitSelesai($siswa, $sebelum);
    }

    /**
     * Ringkasan jalur belajar untuk S-04: tiap pertemuan beserta status.
     *
     * @return Collection<int, array{pertemuan: Meeting, terbuka: bool, selesai: bool, unit_selesai: int, unit_total: int}>
     */
    public function jalur(User $siswa): Collection
    {
        $selesaiPerUnit = $siswa->unitProgress()->where('status', 'selesai')->pluck('lesson_unit_id')->flip();

        return Meeting::query()->with('lessonUnits')->orderBy('urutan')->get()->map(function (Meeting $m) use ($siswa, $selesaiPerUnit): array {
            $total = $m->lessonUnits->count();
            $selesai = $m->lessonUnits->filter(fn (LessonUnit $u) => $selesaiPerUnit->has($u->getKey()))->count();

            return [
                'pertemuan' => $m,
                'terbuka' => $this->pertemuanTerbuka($siswa, $m),
                'selesai' => $total > 0 && $selesai === $total,
                'unit_selesai' => $selesai,
                'unit_total' => $total,
            ];
        });
    }
}
