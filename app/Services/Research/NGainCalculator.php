<?php

namespace App\Services\Research;

/**
 * N-Gain (Hake) untuk keefektifan produk.
 *
 *   N-Gain = (posttest - pretest) / (skor_maks - pretest)
 *
 * CATATAN METODOLOGIS PENTING
 * ---------------------------
 * Kelas ini HANYA menghitung N-Gain dan statistik deskriptif.
 * Uji-t sampel independen sengaja TIDAK dikerjakan di sini, melainkan di
 * SPSS/JASP, agar statistik inferensial yang dilaporkan pada artikel berasal
 * dari perangkat lunak baku yang dapat diaudit reviewer. Sistem hanya
 * menyiapkan data yang bersih dan siap dianalisis.
 */
final class NGainCalculator
{
    public function __construct(
        private readonly float $skorMaks = 100.0,
        /**
         * Pasangan [ambang, label], diperiksa berurutan dari yang tertinggi.
         * Bukan array asosiatif berkunci float -- lihat catatan pada AikenCalculator.
         */
        private readonly array $kategori = [
            [0.70, 'tinggi'],
            [0.30, 'sedang'],
            [0.00, 'rendah'],
        ],
    ) {}

    public function hitung(float $pretest, float $posttest): array
    {
        if ($pretest >= $this->skorMaks) {
            // Tidak ada ruang perbaikan; N-Gain tidak terdefinisi.
            return ['n_gain' => null, 'kategori' => 'tak_terdefinisi', 'catatan' => 'pretest sudah maksimum'];
        }

        $g = ($posttest - $pretest) / ($this->skorMaks - $pretest);

        return [
            'n_gain' => round($g, 3),
            'kategori' => $this->kategorikan($g),
            'pretest' => $pretest,
            'posttest' => $posttest,
        ];
    }

    /**
     * @param  array<int, array{pretest: float, posttest: float}>  $data
     */
    public function ringkasKelompok(array $data): array
    {
        $nilai = [];
        foreach ($data as $baris) {
            $h = $this->hitung((float) $baris['pretest'], (float) $baris['posttest']);
            if ($h['n_gain'] !== null) {
                $nilai[] = $h['n_gain'];
            }
        }

        $n = count($nilai);
        if ($n === 0) {
            return ['n' => 0, 'rerata' => null, 'sd' => null, 'min' => null, 'maks' => null, 'kategori' => null];
        }

        $rerata = array_sum($nilai) / $n;
        $varians = $n > 1
            ? array_sum(array_map(static fn ($x) => ($x - $rerata) ** 2, $nilai)) / ($n - 1)
            : 0.0;

        return [
            'n' => $n,
            'rerata' => round($rerata, 3),
            'sd' => round(sqrt($varians), 3),
            'min' => round(min($nilai), 3),
            'maks' => round(max($nilai), 3),
            'kategori' => $this->kategorikan($rerata),
        ];
    }

    private function kategorikan(float $g): string
    {
        foreach ($this->kategori as [$batas, $label]) {
            if ($g >= $batas) {
                return $label;
            }
        }

        return 'rendah';
    }
}
