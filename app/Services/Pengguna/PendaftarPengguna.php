<?php

namespace App\Services\Pengguna;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Menyimpan hasil copas admin: buat akun baru, atau tambahkan peran
 * pada username yang sudah ada. Username tidak digandakan.
 */
final class PendaftarPengguna
{
    /**
     * @param  list<array{peran: Peran, username: string, nama: string, password: string}>  $baris
     * @return array{dibuat: int, sudah_ada: int, galat: list<string>}
     */
    public function daftarkanBanyak(array $baris): array
    {
        $dibuat = 0;
        $sudahAda = 0;
        $galat = [];

        DB::transaction(function () use ($baris, &$dibuat, &$sudahAda): void {
            foreach ($baris as $data) {
                $pengguna = User::query()->where('username', $data['username'])->first();

                if ($pengguna === null) {
                    $pengguna = $this->buat($data);
                    $dibuat++;
                } else {
                    $sudahAda++;
                }

                $pengguna->berikanPeran($data['peran']);

                if ($data['peran'] === Peran::Siswa && $pengguna->kode_anonim === null) {
                    $pengguna->forceFill(['kode_anonim' => User::kodeAnonimBerikutnya('S')])->save();
                }
            }
        });

        return ['dibuat' => $dibuat, 'sudah_ada' => $sudahAda, 'galat' => $galat];
    }

    /**
     * @param  array{peran: Peran, username: string, nama: string, password: string}  $data
     */
    private function buat(array $data): User
    {
        $siswa = $data['peran'] === Peran::Siswa;

        return User::query()->create([
            'nama' => $data['nama'],
            'username' => $data['username'],
            'password' => $data['password'],
            'pin_kartu' => $siswa ? $data['password'] : null,
            'kode_anonim' => $siswa ? User::kodeAnonimBerikutnya('S') : null,
            'aktif' => true,
        ]);
    }
}
