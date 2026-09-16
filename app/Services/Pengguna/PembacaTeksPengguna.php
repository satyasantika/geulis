<?php

namespace App\Services\Pengguna;

use App\Enums\Peran;

/**
 * Membaca copas daftar pengguna admin menjadi baris siap daftar.
 * Logika murni: format `role, username, nama, password` diuji tanpa basis data.
 *
 * Username: NIS (siswa) atau NIP/inisial (guru). Pemisah koma, tab, atau titik koma.
 */
final class PembacaTeksPengguna
{
    /**
     * @return array{baris: list<array{peran: Peran, username: string, nama: string, password: string}>, galat: list<string>}
     */
    public function baca(string $teks): array
    {
        $teks = preg_replace('/^\xEF\xBB\xBF/', '', $teks) ?? $teks;
        $barisMentah = preg_split('/\r\n|\r|\n/', trim($teks)) ?: [];
        $baris = [];
        $galat = [];
        $usernameTerlihat = [];
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
                $galat[] = "Baris {$nomor}: tulis role, username, nama, password (contoh: siswa,0056781234,Reza Pratama,siswa-1234).";

                continue;
            }

            $peran = $this->peran($uraian['role']);
            if ($peran === null) {
                $galat[] = "Baris {$nomor}: peran '{$uraian['role']}' tidak dikenal. Gunakan siswa, guru, validator, observer, peneliti, atau admin.";

                continue;
            }

            if (isset($usernameTerlihat[$uraian['username']])) {
                $galat[] = "Baris {$nomor}: username {$uraian['username']} muncul dua kali di daftar.";

                continue;
            }
            $usernameTerlihat[$uraian['username']] = true;

            $baris[] = [
                'peran' => $peran,
                'username' => $uraian['username'],
                'nama' => $uraian['nama'],
                'password' => $uraian['password'],
            ];
        }

        return ['baris' => $baris, 'galat' => $galat];
    }

    /**
     * @return array{role: string, username: string, nama: string, password: string}|null
     */
    private function uraikan(string $mentah): ?array
    {
        $sel = $this->sel($mentah);
        if (count($sel) < 4) {
            return null;
        }

        $role = $sel[0];
        $username = $this->username($sel[1]);
        $password = $sel[count($sel) - 1];
        $nama = trim(implode(' ', array_slice($sel, 2, -1)));

        if ($role === '' || $username === '' || $nama === '' || $password === '') {
            return null;
        }
        if (strlen($username) > 50 || strlen($nama) > 255 || strlen($password) > 72) {
            return null;
        }

        return ['role' => $role, 'username' => $username, 'nama' => $nama, 'password' => $password];
    }

    /**
     * @return list<string>
     */
    private function sel(string $mentah): array
    {
        $mentah = trim($mentah);
        if (preg_match('/[\t;,]/', $mentah) === 1) {
            $sel = preg_split('/[\t;,]+/', $mentah) ?: [];
        } else {
            $sel = preg_split('/\s+/', $mentah) ?: [];
        }

        return array_values(array_filter(
            array_map(fn (string $s): string => trim($s, " \t\"'"), $sel),
            fn (string $s): bool => $s !== '',
        ));
    }

    private function username(string $mentah): string
    {
        $mentah = trim($mentah);
        if ($mentah !== '' && preg_match('/^[\d\s.\-]+$/', $mentah) === 1) {
            return preg_replace('/\D/', '', $mentah) ?? $mentah;
        }

        return $mentah;
    }

    private function peran(string $role): ?Peran
    {
        $kunci = strtolower(trim($role));

        return Peran::tryFrom($kunci);
    }

    private function judul(string $mentah): bool
    {
        $sel = $this->sel($mentah);
        if ($sel === []) {
            return false;
        }

        $diizinkan = ['role', 'peran', 'username', 'user', 'nama', 'password', 'sandi', 'pin', 'nis', 'nip'];
        foreach ($sel as $kata) {
            $huruf = strtolower(preg_replace('/[^a-z]/i', '', $kata) ?? '');
            if ($huruf === '' || ! in_array($huruf, $diizinkan, true)) {
                return false;
            }
        }

        return true;
    }
}
