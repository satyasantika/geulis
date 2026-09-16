<?php

namespace App\Services\Kelas;

/**
 * Membaca copas NIS + nama dari guru menjadi baris siap daftar.
 * Logika murni: tanpa Eloquent, supaya formatnya bisa diuji tanpa basis data.
 *
 * Satu baris satu siswa. Pemisah tab, koma, titik koma, atau spasi.
 * Urutan NIS–nama atau nama–NIS. Kolom L/P dan kode sekolah diabaikan.
 */
final class PembacaTeksSiswa
{
    /**
     * @return array{baris: list<array{nama: string, nis: string, jenis_kelamin: string|null}>, galat: list<string>}
     */
    public function baca(string $teks): array
    {
        $teks = preg_replace('/^\xEF\xBB\xBF/', '', $teks) ?? $teks;
        $barisMentah = preg_split('/\r\n|\r|\n/', trim($teks)) ?: [];
        $baris = [];
        $galat = [];
        $nisTerlihat = [];
        $judulDilewati = false;

        foreach ($barisMentah as $i => $mentah) {
            $nomor = $i + 1;
            if (trim($mentah) === '') {
                continue;
            }

            if (! $judulDilewati && $this->judul($mentah)) {
                $judulDilewati = true;

                continue;
            }

            $uraian = $this->uraikan($mentah);
            if ($uraian === null) {
                $galat[] = "Baris {$nomor}: tulis NIS dan nama (contoh: 0056781234 Reza Pratama).";

                continue;
            }

            if (isset($nisTerlihat[$uraian['nis']])) {
                $galat[] = "Baris {$nomor}: NIS {$uraian['nis']} muncul dua kali di daftar.";

                continue;
            }
            $nisTerlihat[$uraian['nis']] = true;

            $baris[] = [
                'nama' => $uraian['nama'],
                'nis' => $uraian['nis'],
                'jenis_kelamin' => null,
            ];
        }

        return ['baris' => $baris, 'galat' => $galat];
    }

    /**
     * @return array{nama: string, nis: string}|null
     */
    private function uraikan(string $mentah): ?array
    {
        $sel = preg_split('/[\t;,]+/', trim($mentah)) ?: [];
        $sel = array_values(array_filter(
            array_map(fn (string $s): string => trim($s, " \t\"'"), $sel),
            fn (string $s): bool => $s !== '',
        ));

        if ($sel === []) {
            return null;
        }

        if (count($sel) === 1) {
            return $this->uraikanSatuKolom($sel[0]);
        }

        $nis = null;
        $namaBagian = [];
        foreach ($sel as $bagian) {
            $angka = preg_replace('/\D/', '', $bagian) ?? '';
            $hanyaAngka = preg_replace('/[\s.\-]/', '', $bagian) === $angka && $this->nisSah($angka);
            if ($hanyaAngka && $nis === null) {
                $nis = $angka;

                continue;
            }
            if (preg_match('/^[LP]$/i', $bagian) === 1) {
                continue;
            }
            $namaBagian[] = $bagian;
        }

        $nama = $this->pilihNama($namaBagian);
        if ($nis === null || $nama === '') {
            return null;
        }

        return ['nis' => $nis, 'nama' => $nama];
    }

    /**
     * @return array{nama: string, nis: string}|null
     */
    private function uraikanSatuKolom(string $teks): ?array
    {
        if (preg_match('/^(\d{4,20})\s+(\p{L}.*)$/u', $teks, $m) === 1) {
            return ['nis' => $m[1], 'nama' => trim($m[2])];
        }
        if (preg_match('/^(\p{L}.*)\s+(\d{4,20})$/u', $teks, $m) === 1) {
            return ['nis' => $m[2], 'nama' => trim($m[1])];
        }

        return null;
    }

    /**
     * @param  list<string>  $kandidat
     */
    private function pilihNama(array $kandidat): string
    {
        if ($kandidat === []) {
            return '';
        }
        if (count($kandidat) === 1) {
            return $kandidat[0];
        }

        usort($kandidat, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return $kandidat[0];
    }

    private function judul(string $mentah): bool
    {
        if (preg_match('/\d{4,}/', $mentah) === 1) {
            return false;
        }

        $huruf = strtolower(preg_replace('/[^a-z\s]/i', ' ', $mentah) ?? '');
        $huruf = trim(preg_replace('/\s+/', ' ', $huruf) ?? '');
        $token = preg_split('/\s+/', $huruf) ?: [];
        if ($token === [] || $token === ['']) {
            return false;
        }

        $diizinkan = ['nis', 'nama', 'namasiswa', 'jk', 'jeniskelamin', 'jenis', 'kelamin'];
        foreach ($token as $kata) {
            if (! in_array($kata, $diizinkan, true)) {
                return false;
            }
        }

        return in_array('nis', $token, true) || in_array('nama', $token, true);
    }

    private function nisSah(string $nis): bool
    {
        $panjang = strlen($nis);

        return $panjang >= 4 && $panjang <= 20 && ctype_digit($nis);
    }
}
