<?php

namespace App\Services\Research;

/**
 * Skor angket respons (kepraktisan): butir negatif dibalik
 * (skor' = skala_maks + 1 − skor), lalu rerata per aspek dan keseluruhan
 * dikategorikan dengan ambang dari config/angket.php. Logika murni.
 */
final class PenghitungKepraktisan
{
    /**
     * @param  array<int, array{ambang: float, label: string}|array{0: float, 1: string}>  $kategori
     */
    public function __construct(
        private readonly int $skalaMaks = 4,
        private readonly array $kategori = [[3.25, 'sangat praktis'], [2.50, 'praktis'], [1.75, 'kurang praktis'], [0.0, 'tidak praktis']],
    ) {}

    /**
     * @param  list<array{aspek: string, butir_negatif: bool, skor: list<int>}>  $butir  skor = jawaban semua responden
     * @return array{n_responden: int, rerata: float|null, kategori: string|null, per_aspek: array<string, array{rerata: float, kategori: string, n_butir: int}>}
     */
    public function rekap(array $butir): array
    {
        $perAspek = [];
        $semua = [];
        $nResponden = 0;

        foreach ($butir as $b) {
            $nResponden = max($nResponden, count($b['skor']));
            foreach ($b['skor'] as $s) {
                $nilai = $b['butir_negatif'] ? $this->skalaMaks + 1 - (int) $s : (int) $s;
                $perAspek[$b['aspek']]['nilai'][] = $nilai;
                $semua[] = $nilai;
            }
            $perAspek[$b['aspek']]['n_butir'] = ($perAspek[$b['aspek']]['n_butir'] ?? 0) + 1;
        }

        $hasilAspek = [];
        foreach ($perAspek as $aspek => $d) {
            $rerata = ($d['nilai'] ?? []) === [] ? 0.0 : array_sum($d['nilai']) / count($d['nilai']);
            $hasilAspek[$aspek] = ['rerata' => round($rerata, 2), 'kategori' => $this->kategorikan($rerata), 'n_butir' => $d['n_butir']];
        }

        $rerata = $semua === [] ? null : array_sum($semua) / count($semua);

        return [
            'n_responden' => $nResponden,
            'rerata' => $rerata === null ? null : round($rerata, 2),
            'kategori' => $rerata === null ? null : $this->kategorikan($rerata),
            'per_aspek' => $hasilAspek,
        ];
    }

    private function kategorikan(float $x): string
    {
        foreach ($this->kategori as [$batas, $label]) {
            if ($x >= $batas) {
                return $label;
            }
        }

        return 'tidak praktis';
    }
}
