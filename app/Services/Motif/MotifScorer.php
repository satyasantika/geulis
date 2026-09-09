<?php

namespace App\Services\Motif;

/**
 * Penskoran Motif Builder — komponen orisinal, inti nilai HKI.
 *
 * Siswa menyusun urutan perintah transformasi untuk merekonstruksi motif
 * sasaran. Sistem menjalankan perintah itu pada kisi raster, lalu
 * membandingkan hasilnya dengan sasaran memakai Intersection over Union.
 *
 *   Kemiripan = (luas irisan / luas gabungan) * 100     -> lolos bila >= 90
 *   Efisiensi = (langkah_minimum / langkah_siswa) * 100 -> dilaporkan terpisah
 *
 * Efisiensi TIDAK menggugurkan kelulusan. Algoritma yang benar tetapi bertele-tele
 * adalah capaian yang sah; keringkasan adalah bonus, bukan syarat.
 */
final class MotifScorer
{
    public function __construct(
        private readonly int $resolusi = 200,
        private readonly float $ambangLolos = 90.0,
    ) {}

    /**
     * @param  bool[]  $rasterSiswa  kisi resolusi^2, true = terisi
     * @param  bool[]  $rasterSasaran
     */
    public function nilai(array $rasterSiswa, array $rasterSasaran, int $langkahSiswa, int $langkahMinimum): array
    {
        $kemiripan = $this->iou($rasterSiswa, $rasterSasaran) * 100;
        $efisiensi = $langkahSiswa > 0
            ? min(100.0, ($langkahMinimum / $langkahSiswa) * 100)
            : 0.0;

        return [
            'skor_kemiripan' => round($kemiripan, 2),
            'lolos' => $kemiripan >= $this->ambangLolos,
            'langkah_siswa' => $langkahSiswa,
            'langkah_minimum' => $langkahMinimum,
            'efisiensi' => round($efisiensi, 2),
        ];
    }

    private function iou(array $a, array $b): float
    {
        $irisan = 0;
        $gabungan = 0;
        $n = min(count($a), count($b));

        for ($i = 0; $i < $n; $i++) {
            $x = (bool) $a[$i];
            $y = (bool) $b[$i];
            if ($x && $y) {
                $irisan++;
            }
            if ($x || $y) {
                $gabungan++;
            }
        }

        return $gabungan === 0 ? 0.0 : $irisan / $gabungan;
    }

    /**
     * Ekstraksi bukti empat indikator CT dari satu kiriman Motif Builder.
     * Inilah yang menjembatani "transformasi geometri" dan "computational thinking".
     *
     * @param  array  $urutanPerintah  [{op:"ROTASI", pusat:[0,0], sudut:45}, ...]
     * @param  array  $kunci  jawaban acuan untuk unit ini
     */
    public function buktiCT(array $urutanPerintah, array $motifDasarDitandai, array $kunci): array
    {
        $nPengulangan = 0;
        $parameterBenar = 0;
        $parameterTotal = 0;

        foreach ($urutanPerintah as $perintah) {
            if (($perintah['op'] ?? '') === 'ULANGI') {
                $nPengulangan = (int) ($perintah['n'] ?? 0);
            }
            foreach (['sudut', 'k', 'pusat', 'vektor', 'garis'] as $param) {
                if (array_key_exists($param, $perintah)) {
                    $parameterTotal++;
                    if ($this->parameterCocok($perintah, $param, $kunci)) {
                        $parameterBenar++;
                    }
                }
            }
        }

        return [
            // Dekomposisi: ketepatan menandai motif dasar sebelum membangun
            'dekomposisi' => [
                'ditandai' => count($motifDasarDitandai),
                'diharapkan' => count($kunci['motif_dasar'] ?? []),
                'tepat' => $this->irisanTepat($motifDasarDitandai, $kunci['motif_dasar'] ?? []),
            ],
            // Pengenalan pola: menetapkan banyaknya pengulangan
            'pengenalan_pola' => [
                'n_pengulangan' => $nPengulangan,
                'benar' => $nPengulangan === (int) ($kunci['n_pengulangan'] ?? -1),
            ],
            // Abstraksi: ketepatan parameter (sudut, faktor skala, pusat, vektor)
            'abstraksi' => [
                'parameter_benar' => $parameterBenar,
                'parameter_total' => $parameterTotal,
                'proporsi' => $parameterTotal > 0 ? round($parameterBenar / $parameterTotal, 3) : 0,
            ],
            // Algoritma: dinilai dari kemiripan + efisiensi (lihat nilai())
            'algoritma' => [
                'jumlah_langkah' => count($urutanPerintah),
                'memakai_perulangan' => $nPengulangan > 0,
            ],
        ];
    }

    private function parameterCocok(array $perintah, string $param, array $kunci): bool
    {
        $acuan = $kunci['parameter'][$perintah['op']][$param] ?? null;

        if ($acuan === null) {
            return false;
        }

        if (is_numeric($acuan) && is_numeric($perintah[$param])) {
            return abs((float) $perintah[$param] - (float) $acuan) < 0.001;
        }

        return $perintah[$param] == $acuan;
    }

    private function irisanTepat(array $ditandai, array $kunci): int
    {
        $cocok = 0;
        foreach ($ditandai as $d) {
            if (in_array($d, $kunci, false)) {
                $cocok++;
            }
        }

        return $cocok;
    }
}
