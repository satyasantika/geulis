<?php

namespace App\Services\Motif;

use InvalidArgumentException;

/**
 * Mesin transformasi Motif Builder (sisi server) — komponen orisinal.
 *
 * Menjalankan urutan perintah pada poligon (koordinat kisi) dan menghasilkan
 * seluruh bangun yang tergambar di kanvas. Semantik "hasil terakhir":
 *
 *   MOTIF_DASAR              kanvas = {motif}; kini = {motif}
 *   TRANSLASI/REFLEKSI/      kini' = T(kini); kanvas += kini'; kini = kini'
 *   ROTASI/DILATASI
 *   ULANGI n KALI { badan }  jalankan badan n kali; setelahnya kini = semua
 *                            bangun yang lahir selama perulangan (+ kini awal),
 *                            supaya lapisan berikutnya bekerja pada kelompok.
 *
 * Versi JavaScript (resources/js/motif-builder.js) meniru semantik ini
 * persis; skor akhir SELALU dihitung dari mesin ini, bukan dari klien.
 *
 * Bentuk perintah:
 *   {op:"MOTIF_DASAR"} · {op:"TRANSLASI", vektor:[a,b]} · {op:"REFLEKSI", garis:"x"|"y"|"y=x"|"y=-x"}
 *   {op:"ROTASI", pusat:[x,y], sudut:derajat} · {op:"DILATASI", pusat:[x,y], k:faktor}
 *   {op:"ULANGI", n:int, badan:[...]}
 */
final class MesinTransformasi
{
    public const array OPERASI = ['MOTIF_DASAR', 'TRANSLASI', 'REFLEKSI', 'ROTASI', 'DILATASI', 'ULANGI'];

    public const array GARIS = ['x', 'y', 'y=x', 'y=-x'];

    private const int MAKS_BANGUN = 400;

    /**
     * @param  list<array{float, float}>  $motifDasar  poligon
     * @param  list<array<string, mixed>>  $perintah
     * @return list<list<array{float, float}>> poligon-poligon di kanvas
     */
    public function jalankan(array $motifDasar, array $perintah): array
    {
        $kanvas = [];
        $kini = [];

        $this->eksekusi($perintah, $motifDasar, $kanvas, $kini, 0);

        return $kanvas;
    }

    /**
     * Banyaknya langkah (blok) yang ditulis siswa — MOTIF_DASAR tidak dihitung,
     * ULANGI dihitung satu ditambah isi badannya.
     *
     * @param  list<array<string, mixed>>  $perintah
     */
    public function hitungLangkah(array $perintah): int
    {
        $n = 0;
        foreach ($perintah as $p) {
            $op = $p['op'] ?? '';
            if ($op === 'MOTIF_DASAR') {
                continue;
            }
            $n++;
            if ($op === 'ULANGI') {
                $n += $this->hitungLangkah($p['badan'] ?? []);
            }
        }

        return $n;
    }

    /**
     * Daftar perintah rata (ULANGI diikuti isi badannya) untuk buktiCT().
     *
     * @param  list<array<string, mixed>>  $perintah
     * @return list<array<string, mixed>>
     */
    public function ratakan(array $perintah): array
    {
        $hasil = [];
        foreach ($perintah as $p) {
            $hasil[] = $p;
            if (($p['op'] ?? '') === 'ULANGI') {
                $hasil = [...$hasil, ...$this->ratakan($p['badan'] ?? [])];
            }
        }

        return $hasil;
    }

    /**
     * Memeriksa bentuk perintah; melempar InvalidArgumentException dengan
     * pesan yang bisa ditampilkan ke siswa.
     *
     * @param  list<array<string, mixed>>  $perintah
     */
    public function validasi(array $perintah, int $kedalaman = 0): void
    {
        if ($kedalaman > 2) {
            throw new InvalidArgumentException('Perulangan bersarang maksimal dua tingkat.');
        }

        foreach ($perintah as $p) {
            $op = $p['op'] ?? null;
            if (! in_array($op, self::OPERASI, true)) {
                throw new InvalidArgumentException("Perintah tidak dikenal: {$op}");
            }

            match ($op) {
                'TRANSLASI' => $this->pastikanTitik($p['vektor'] ?? null, 'vektor translasi'),
                'REFLEKSI' => in_array($p['garis'] ?? null, self::GARIS, true) ?: throw new InvalidArgumentException('Garis refleksi harus x, y, y=x, atau y=-x.'),
                'ROTASI' => [$this->pastikanTitik($p['pusat'] ?? null, 'pusat rotasi'), is_numeric($p['sudut'] ?? null) ?: throw new InvalidArgumentException('Sudut rotasi harus angka.')],
                'DILATASI' => [$this->pastikanTitik($p['pusat'] ?? null, 'pusat dilatasi'), (is_numeric($p['k'] ?? null) && (float) $p['k'] !== 0.0) ?: throw new InvalidArgumentException('Faktor skala k harus angka bukan nol.')],
                'ULANGI' => [
                    (is_int($p['n'] ?? null) && $p['n'] >= 1 && $p['n'] <= 36) ?: throw new InvalidArgumentException('Banyak pengulangan harus bilangan bulat 1–36.'),
                    $this->validasi($p['badan'] ?? [], $kedalaman + 1),
                ],
                default => null,
            };
        }
    }

    // ---------------------------------------------------------------

    /**
     * @param  list<array<string, mixed>>  $perintah
     * @param  list<array{float, float}>  $motifDasar
     * @param  list<list<array{float, float}>>  $kanvas
     * @param  list<list<array{float, float}>>  $kini
     */
    private function eksekusi(array $perintah, array $motifDasar, array &$kanvas, array &$kini, int $kedalaman): void
    {
        foreach ($perintah as $p) {
            $op = $p['op'] ?? '';

            if ($op === 'MOTIF_DASAR') {
                $kanvas[] = $motifDasar;
                $kini = [$motifDasar];

                continue;
            }

            if ($op === 'ULANGI') {
                $lahir = $kini;
                $badan = $p['badan'] ?? [];
                for ($i = 0; $i < (int) $p['n']; $i++) {
                    $this->eksekusi($badan, $motifDasar, $kanvas, $kini, $kedalaman + 1);
                    $lahir = [...$lahir, ...$kini];
                }
                $kini = $lahir;

                continue;
            }

            $baru = [];
            foreach ($kini as $poligon) {
                $baru[] = array_map(fn (array $t): array => $this->petakanTitik($op, $p, (float) $t[0], (float) $t[1]), $poligon);
            }

            if (count($kanvas) + count($baru) > self::MAKS_BANGUN) {
                throw new InvalidArgumentException('Terlalu banyak bangun; kurangi pengulangan.');
            }

            $kanvas = [...$kanvas, ...$baru];
            $kini = $baru;
        }
    }

    /**
     * @param  array<string, mixed>  $p
     * @return array{float, float}
     */
    private function petakanTitik(string $op, array $p, float $x, float $y): array
    {
        return match ($op) {
            'TRANSLASI' => [$x + (float) $p['vektor'][0], $y + (float) $p['vektor'][1]],
            'REFLEKSI' => match ($p['garis']) {
                'x' => [$x, -$y],
                'y' => [-$x, $y],
                'y=x' => [$y, $x],
                'y=-x' => [-$y, -$x],
            },
            'ROTASI' => $this->rotasi($x, $y, (float) $p['pusat'][0], (float) $p['pusat'][1], (float) $p['sudut']),
            'DILATASI' => [
                (float) $p['pusat'][0] + (float) $p['k'] * ($x - (float) $p['pusat'][0]),
                (float) $p['pusat'][1] + (float) $p['k'] * ($y - (float) $p['pusat'][1]),
            ],
            default => throw new InvalidArgumentException("Perintah tidak dikenal: {$op}"),
        };
    }

    /** @return array{float, float} */
    private function rotasi(float $x, float $y, float $cx, float $cy, float $derajat): array
    {
        $rad = deg2rad($derajat);
        $dx = $x - $cx;
        $dy = $y - $cy;

        return [
            round($cx + $dx * cos($rad) - $dy * sin($rad), 9),
            round($cy + $dx * sin($rad) + $dy * cos($rad), 9),
        ];
    }

    private function pastikanTitik(mixed $t, string $nama): true
    {
        if (! is_array($t) || count($t) !== 2 || ! is_numeric($t[0] ?? null) || ! is_numeric($t[1] ?? null)) {
            throw new InvalidArgumentException("{$nama} harus berupa pasangan angka.");
        }

        return true;
    }
}
