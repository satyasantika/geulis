<?php

namespace App\Services\Research;

use App\Models\Classroom;
use App\Models\CtTest;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * P-02: N-Gain per siswa, per kelas, per kelompok, dan per indikator CT,
 * beserta statistik deskriptif — memakai NGainCalculator (murni).
 * HANYA siswa yang bersedia diteliti (S-02 "ya") yang masuk hitungan.
 * Uji-t TIDAK dihitung di sini; datanya diekspor untuk SPSS/JASP.
 */
final class AnalitikPenelitian
{
    public function __construct(private readonly NGainCalculator $ngain = new NGainCalculator) {}

    /**
     * @return Collection<int, array{siswa: User, kelas: Classroom|null, kelompok: string, pretest: float, posttest: float, n_gain: float|null, kategori: string, per_indikator: array<string, float|null>}>
     */
    public function perSiswa(): Collection
    {
        $pre = CtTest::query()->where('jenis', 'pretest')->first();
        $post = CtTest::query()->where('jenis', 'posttest')->first();
        if ($pre === null || $post === null) {
            return collect();
        }

        $maksPre = $this->maksPerIndikator($pre);
        $maksPost = $this->maksPerIndikator($post);

        $siswa = User::query()->bersediaDiteliti()
            ->whereHas('roles', fn ($q) => $q->where('nama', 'siswa'))
            ->with(['enrollments.classroom', 'ctScores' => fn ($q) => $q->whereNotNull('dihitung_pada')])
            ->get();

        return $siswa->map(function (User $s) use ($pre, $post, $maksPre, $maksPost): ?array {
            $skorPre = $s->ctScores->firstWhere('ct_test_id', $pre->id);
            $skorPost = $s->ctScores->firstWhere('ct_test_id', $post->id);
            if ($skorPre === null || $skorPost === null) {
                return null;
            }

            $h = $this->ngain->hitung((float) $skorPre->persen, (float) $skorPost->persen);
            $enrollment = $s->enrollments->firstWhere('status', 'aktif') ?? $s->enrollments->first();

            $perIndikator = [];
            foreach (['D' => 'skor_d', 'P' => 'skor_p', 'A' => 'skor_a', 'Al' => 'skor_al'] as $kode => $kolom) {
                $pPre = ($maksPre[$kode] ?? 0) > 0 ? $skorPre->{$kolom} / $maksPre[$kode] * 100 : null;
                $pPost = ($maksPost[$kode] ?? 0) > 0 ? $skorPost->{$kolom} / $maksPost[$kode] * 100 : null;
                $perIndikator[$kode] = ($pPre === null || $pPost === null) ? null : $this->ngain->hitung($pPre, $pPost)['n_gain'];
                $perIndikator[$kode.'_pre'] = $pPre;
                $perIndikator[$kode.'_post'] = $pPost;
            }

            return [
                'siswa' => $s,
                'kelas' => $enrollment?->classroom,
                'kelompok' => $enrollment?->classroom?->kelompok_riset ?? 'non_riset',
                'pretest' => (float) $skorPre->persen,
                'posttest' => (float) $skorPost->persen,
                'n_gain' => $h['n_gain'],
                'kategori' => $h['kategori'],
                'per_indikator' => $perIndikator,
            ];
        })->filter()->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $baris
     * @return array<string, array{n: int, rerata: float|null, sd: float|null, min: float|null, maks: float|null, kategori: string|null}>
     */
    public function perKelompok(Collection $baris): array
    {
        return $baris->groupBy('kelompok')
            ->map(fn ($g) => $this->ngain->ringkasKelompok($g->map(fn ($b) => ['pretest' => $b['pretest'], 'posttest' => $b['posttest']])->all()))
            ->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $baris
     * @return array<string, array<string, float|null>> kode indikator → [pre_E, post_E, ngain_E, ngain_K]
     */
    public function perIndikator(Collection $baris): array
    {
        $hasil = [];
        foreach (['D', 'P', 'A', 'Al'] as $kode) {
            $e = $baris->where('kelompok', 'eksperimen');
            $k = $baris->where('kelompok', 'kontrol');
            $hasil[$kode] = [
                'pre_e' => $this->rerata($e->pluck('per_indikator.'.$kode.'_pre')),
                'post_e' => $this->rerata($e->pluck('per_indikator.'.$kode.'_post')),
                'ngain_e' => $this->rerata($e->pluck('per_indikator.'.$kode)),
                'ngain_k' => $this->rerata($k->pluck('per_indikator.'.$kode)),
            ];
        }

        return $hasil;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $baris
     * @return array<string, array{n: int, rerata: float|null, sd: float|null, min: float|null, maks: float|null, kategori: string|null}>
     */
    public function perKelas(Collection $baris): array
    {
        return $baris->groupBy(fn ($b) => $b['kelas']?->nama ?? '—')
            ->map(fn ($g) => $this->ngain->ringkasKelompok($g->map(fn ($b) => ['pretest' => $b['pretest'], 'posttest' => $b['posttest']])->all()))
            ->all();
    }

    /** @return array<string, int> */
    private function maksPerIndikator(CtTest $tes): array
    {
        return $tes->items->groupBy('indikator')->map(fn ($g) => (int) $g->sum('skor_maks'))->all();
    }

    private function rerata(Collection $nilai): ?float
    {
        $bersih = $nilai->filter(fn ($v) => $v !== null);

        return $bersih->isEmpty() ? null : round($bersih->avg(), 3);
    }
}
