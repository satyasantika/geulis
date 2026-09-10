<?php

namespace App\Services\Kelas;

/**
 * Membaca CSV daftar siswa dari guru menjadi baris-baris siap daftar.
 * Logika murni: menerima teks, mengembalikan baris sah dan daftar galat
 * per nomor baris supaya guru bisa memperbaiki berkasnya sendiri.
 *
 * Format yang diterima (brief Sprint 0 butir 6): kolom `nama`, `nis`,
 * `jenis_kelamin`. Baris pertama boleh judul kolom (urutan bebas) atau
 * langsung data dengan urutan nama;nis;jenis_kelamin. Pemisah koma atau
 * titik koma — Excel berbahasa Indonesia menyimpan CSV dengan titik koma.
 */
final class PembacaCsvSiswa
{
    private const KOLOM = ['nama', 'nis', 'jenis_kelamin'];

    /**
     * @return array{baris: list<array{nama: string, nis: string, jenis_kelamin: string|null}>, galat: list<string>}
     */
    public function baca(string $teks): array
    {
        $teks = preg_replace('/^\xEF\xBB\xBF/', '', $teks) ?? $teks; // BOM dari Excel
        $barisMentah = preg_split('/\r\n|\r|\n/', trim($teks)) ?: [];
        $pemisah = substr_count($teks, ';') > substr_count($teks, ',') ? ';' : ',';

        $baris = [];
        $galat = [];
        $peta = null;
        $nisTerlihat = [];

        foreach ($barisMentah as $i => $mentah) {
            $nomor = $i + 1;
            if (trim($mentah) === '') {
                continue;
            }

            $sel = array_map(fn (string $s): string => trim($s, " \t\"'"), str_getcsv($mentah, $pemisah, '"', '\\'));

            if ($peta === null) {
                $peta = $this->petaKolom($sel);
                if ($peta !== null) {
                    continue; // baris judul
                }
                $peta = [0, 1, 2];
            }

            $nama = $sel[$peta[0]] ?? '';
            $nis = preg_replace('/\D/', '', $sel[$peta[1]] ?? '') ?? '';
            $jk = strtoupper(substr($sel[$peta[2]] ?? '', 0, 1));

            if ($nama === '' || $nis === '') {
                $galat[] = "Baris {$nomor}: nama dan NIS wajib diisi.";

                continue;
            }
            if (isset($nisTerlihat[$nis])) {
                $galat[] = "Baris {$nomor}: NIS {$nis} muncul dua kali di berkas.";

                continue;
            }
            $nisTerlihat[$nis] = true;

            $baris[] = [
                'nama' => $nama,
                'nis' => $nis,
                'jenis_kelamin' => in_array($jk, ['L', 'P'], true) ? $jk : null,
            ];
        }

        return ['baris' => $baris, 'galat' => $galat];
    }

    /**
     * @param  list<string>  $sel
     * @return array{0:int,1:int,2:int}|null
     */
    private function petaKolom(array $sel): ?array
    {
        $huruf = array_map(fn (string $s): string => strtolower(preg_replace('/[^a-z_]/i', '', $s) ?? ''), $sel);
        $indeks = [];
        foreach (self::KOLOM as $kolom) {
            $pos = array_search($kolom, $huruf, true);
            if ($pos === false && $kolom === 'jenis_kelamin') {
                $pos = array_search('jk', $huruf, true);
            }
            if ($pos === false) {
                return null;
            }
            $indeks[] = $pos;
        }

        return $indeks;
    }
}
