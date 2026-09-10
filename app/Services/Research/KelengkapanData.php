<?php

namespace App\Services\Research;

use App\Models\Classroom;
use App\Models\CtTest;
use App\Models\LessonUnit;
use App\Models\LessonUnitProgress;
use App\Models\Meeting;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Illuminate\Support\Collection;

/**
 * P-01 Papan kelengkapan data per kelas: berapa siswa sudah asesmen awal,
 * pretest, selesai P1–P5, posttest, angket. Data bolong yang baru ketahuan
 * saat analisis adalah penyebab paling umum molornya penelitian.
 */
final class KelengkapanData
{
    /**
     * @return Collection<int, array{kelas: Classroom, n: int, asesmen: int, pretest: int, pertemuan: array<int, int>, posttest: int, angket: int, peringatan: list<string>}>
     */
    public function perKelas(): Collection
    {
        $pre = CtTest::query()->where('jenis', 'pretest')->first();
        $post = CtTest::query()->where('jenis', 'posttest')->first();
        $angket = Questionnaire::query()->where('sasaran', 'siswa')->first();
        $meetings = Meeting::query()->with('lessonUnits')->orderBy('urutan')->get();
        $p1Terbit = $meetings->firstWhere('urutan', 1)?->terbit ?? false;

        return Classroom::query()->with(['school', 'siswa' => fn ($q) => $q->with(['placement', 'ctScores', 'consent'])])
            ->where('kelompok_riset', '!=', 'non_riset')->orderBy('school_id')->orderBy('nama')->get()
            ->map(function (Classroom $kelas) use ($pre, $post, $angket, $meetings, $p1Terbit): array {
                $siswa = $kelas->siswa->where('pivot.status', 'aktif');
                $id = $siswa->pluck('id');
                $n = $siswa->count();

                $selesaiUnit = LessonUnitProgress::query()->whereIn('user_id', $id)->where('status', 'selesai')->get()->groupBy('user_id');
                $pertemuan = [];
                foreach ($meetings as $m) {
                    $idUnit = $m->lessonUnits->pluck('id');
                    $pertemuan[$m->urutan] = $idUnit->isEmpty() ? 0 : $selesaiUnit->filter(fn ($g) => $g->whereIn('lesson_unit_id', $idUnit)->count() === $idUnit->count())->count();
                }

                $hitungTes = fn (?CtTest $t) => $t === null ? 0 : $siswa->filter(fn ($s) => $s->ctScores->firstWhere('ct_test_id', $t->id)?->dikumpulkan_pada !== null)->count();
                $pretest = $hitungTes($pre);
                $posttest = $hitungTes($post);
                $angketN = $angket === null ? 0 : QuestionnaireResponse::query()->whereIn('user_id', $id)
                    ->whereIn('questionnaire_item_id', $angket->items()->pluck('id'))->distinct('user_id')->count('user_id');

                $peringatan = [];
                if ($n > 0 && $p1Terbit && $pretest < $n) {
                    $peringatan[] = sprintf('%d siswa belum mengerjakan pretest. Kejar sekarang — pretest yang hilang tidak bisa diambil ulang setelah pembelajaran dimulai, dan siswa itu gugur dari analisis N-Gain.', $n - $pretest);
                }
                $tanpaPersetujuan = $siswa->filter(fn ($s) => $s->consent === null)->count();
                if ($tanpaPersetujuan > 0) {
                    $peringatan[] = "{$tanpaPersetujuan} siswa belum menjawab persetujuan penelitian.";
                }

                return [
                    'kelas' => $kelas,
                    'n' => $n,
                    'asesmen' => $siswa->filter(fn ($s) => $s->placement !== null)->count(),
                    'pretest' => $pretest,
                    'pertemuan' => $pertemuan,
                    'posttest' => $posttest,
                    'angket' => $angketN,
                    'peringatan' => $peringatan,
                ];
            });
    }

    public function jumlahUnit(): int
    {
        return LessonUnit::query()->count();
    }
}
