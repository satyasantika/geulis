<?php

namespace App\Services\Research;

/**
 * Indeks validitas isi Aiken's V.
 *
 *   V = SUM(s) / [ n * (c - 1) ],  dengan s = r - l0
 *
 *   r  = skor penilai
 *   l0 = skor terendah yang mungkin (umumnya 1)
 *   c  = banyaknya kategori skor (mis. 5 untuk skala 1-5)
 *   n  = banyaknya penilai
 *
 * Rujukan: Aiken (1985); Retnawati (2016) — sesuai yang disitasi pada proposal.
 *
 * Kriteria kategorisasi disimpan sebagai parameter, BUKAN ditanam di kode,
 * karena tim mungkin memakai tabel kategorisasi dari rujukan yang berbeda.
 */
final class AikenCalculator
{
    public function __construct(
        private readonly int $skalaMaks = 5,
        private readonly int $skorTerendah = 1,
        /**
         * Pasangan [ambang, label], diperiksa berurutan dari yang tertinggi.
         *
         * CATATAN: sengaja BUKAN array asosiatif [0.80 => 'tinggi'], karena PHP
         * memaksa kunci array bertipe float menjadi integer -- 0.80, 0.40 dan 0.00
         * semuanya runtuh menjadi kunci 0, sehingga seluruh butir akan berkategori
         * sama. Kesalahan seperti ini tidak menimbulkan galat apa pun; ia hanya
         * diam-diam menghasilkan kategori yang salah pada laporan validasi.
         */
        private readonly array $kategori = [
            [0.80, 'tinggi'],
            [0.40, 'sedang'],
            [0.00, 'rendah'],
        ],
    ) {}

    /**
     * @param  int[]  $skorPenilai  skor dari tiap penilai untuk SATU butir
     */
    public function hitungButir(array $skorPenilai): array
    {
        $n = count($skorPenilai);

        if ($n === 0) {
            throw new \InvalidArgumentException('Butir belum dinilai siapa pun.');
        }

        $c = $this->skalaMaks;
        $l0 = $this->skorTerendah;

        foreach ($skorPenilai as $r) {
            if ($r < $l0 || $r > $c) {
                throw new \InvalidArgumentException("Skor {$r} di luar rentang {$l0}..{$c}.");
            }
        }

        $sumS = array_sum(array_map(static fn ($r) => $r - $l0, $skorPenilai));
        $v = $sumS / ($n * ($c - $l0));

        return [
            'nilai_v' => round($v, 3),
            'kategori' => $this->kategorikan($v),
            'n_penilai' => $n,
            'sum_s' => $sumS,
            'pembagi' => $n * ($c - $l0),
        ];
    }

    /**
     * @param  array<int|string, int[]>  $skorPerButir  [id_butir => [skor penilai...]]
     */
    public function hitungBanyakButir(array $skorPerButir): array
    {
        $hasil = [];
        foreach ($skorPerButir as $idButir => $skor) {
            $hasil[$idButir] = $this->hitungButir($skor);
        }

        return $hasil;
    }

    /** Rerata V pada sekumpulan butir (mis. satu aspek, atau keseluruhan instrumen). */
    public function rerata(array $hasilButir): array
    {
        if ($hasilButir === []) {
            return ['nilai_v' => 0.0, 'kategori' => 'rendah', 'n_butir' => 0];
        }

        $rerata = array_sum(array_column($hasilButir, 'nilai_v')) / count($hasilButir);

        return [
            'nilai_v' => round($rerata, 3),
            'kategori' => $this->kategorikan($rerata),
            'n_butir' => count($hasilButir),
        ];
    }

    private function kategorikan(float $v): string
    {
        foreach ($this->kategori as [$batas, $label]) {
            if ($v >= $batas) {
                return $label;
            }
        }

        return 'rendah';
    }
}
