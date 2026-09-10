<?php

namespace App\Services\Motif;

/**
 * Mengubah poligon-poligon (koordinat kisi) menjadi kisi boolean
 * resolusi × resolusi untuk MotifScorer (IoU). Sel dianggap terisi bila
 * titik pusatnya berada di dalam salah satu poligon (aturan genap-ganjil).
 * Sama persis dengan rasterisasi di klien supaya pratinjau kemiripan di
 * HP siswa cocok dengan skor server.
 */
final class Rasterizer
{
    public function __construct(
        private readonly int $resolusi = 200,
        private readonly float $min = -6.0,
        private readonly float $maks = 6.0,
    ) {}

    /**
     * @param  list<list<array{float, float}>>  $poligon
     * @return list<bool> panjang resolusi²
     */
    public function raster(array $poligon): array
    {
        $r = $this->resolusi;
        $lebar = $this->maks - $this->min;
        $sel = $lebar / $r;
        $hasil = array_fill(0, $r * $r, false);

        foreach ($poligon as $titik) {
            if (count($titik) < 3) {
                continue;
            }
            $xs = array_column($titik, 0);
            $ys = array_column($titik, 1);
            // Kotak pembatas → hanya sel di sekitar poligon yang diuji.
            $i0 = max(0, (int) floor((min($xs) - $this->min) / $sel));
            $i1 = min($r - 1, (int) ceil((max($xs) - $this->min) / $sel));
            $j0 = max(0, (int) floor((min($ys) - $this->min) / $sel));
            $j1 = min($r - 1, (int) ceil((max($ys) - $this->min) / $sel));

            for ($j = $j0; $j <= $j1; $j++) {
                $py = $this->min + ($j + 0.5) * $sel;
                for ($i = $i0; $i <= $i1; $i++) {
                    $idx = $j * $r + $i;
                    if ($hasil[$idx]) {
                        continue;
                    }
                    if ($this->diDalam($this->min + ($i + 0.5) * $sel, $py, $titik)) {
                        $hasil[$idx] = true;
                    }
                }
            }
        }

        return $hasil;
    }

    /** @param  list<array{float, float}>  $poligon */
    private function diDalam(float $px, float $py, array $poligon): bool
    {
        $dalam = false;
        $n = count($poligon);
        for ($a = 0, $b = $n - 1; $a < $n; $b = $a++) {
            [$xa, $ya] = [(float) $poligon[$a][0], (float) $poligon[$a][1]];
            [$xb, $yb] = [(float) $poligon[$b][0], (float) $poligon[$b][1]];
            if (($ya > $py) !== ($yb > $py)) {
                $x = ($xb - $xa) * ($py - $ya) / ($yb - $ya) + $xa;
                if ($px < $x) {
                    $dalam = ! $dalam;
                }
            }
        }

        return $dalam;
    }
}
