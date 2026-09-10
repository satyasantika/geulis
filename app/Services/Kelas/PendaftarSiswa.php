<?php

namespace App\Services\Kelas;

use App\Enums\Peran;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Lapisan penyimpanan untuk pendaftaran siswa: membuat akun (NIS + PIN),
 * memberi kode anonim, dan memasukkan ke kelas. Dipakai impor CSV dan
 * formulir tambah satu siswa.
 */
final class PendaftarSiswa
{
    public function __construct(
        private readonly PembangkitPin $pembangkitPin = new PembangkitPin,
    ) {}

    /**
     * @param  list<array{nama: string, nis: string, jenis_kelamin: string|null}>  $baris
     * @return array{dibuat: int, sudah_ada: int, galat: list<string>}
     */
    public function daftarkanBanyak(Classroom $kelas, array $baris): array
    {
        $dibuat = 0;
        $sudahAda = 0;
        $galat = [];

        DB::transaction(function () use ($kelas, $baris, &$dibuat, &$sudahAda, &$galat): void {
            foreach ($baris as $data) {
                $siswa = User::query()->where('username', $data['nis'])->first();

                if ($siswa !== null && ! $siswa->punyaPeran(Peran::Siswa)) {
                    $galat[] = "NIS {$data['nis']} sudah dipakai akun bukan siswa.";

                    continue;
                }

                if ($siswa === null) {
                    $siswa = $this->buatSiswa($kelas, $data);
                    $dibuat++;
                } else {
                    $sudahAda++;
                }

                $kelas->enrollments()->firstOrCreate(['user_id' => $siswa->getKey()]);
            }
        });

        return ['dibuat' => $dibuat, 'sudah_ada' => $sudahAda, 'galat' => $galat];
    }

    /**
     * @param  array{nama: string, nis: string, jenis_kelamin: string|null}  $data
     */
    public function buatSiswa(Classroom $kelas, array $data): User
    {
        $pin = $this->pembangkitPin->baru();

        $siswa = User::query()->create([
            'nama' => $data['nama'],
            'username' => $data['nis'],
            'password' => $pin,
            'pin_kartu' => $pin,
            'school_id' => $kelas->school_id,
            'jenis_kelamin' => $data['jenis_kelamin'],
            'kode_anonim' => User::kodeAnonimBerikutnya('S'),
        ]);

        $siswa->berikanPeran(Peran::Siswa);

        return $siswa;
    }

    /** Atur ulang PIN (G-05 "siswa lupa PIN"). */
    public function aturUlangPin(User $siswa): string
    {
        $pin = $this->pembangkitPin->baru();

        $siswa->forceFill(['password' => $pin, 'pin_kartu' => $pin])->save();

        return $pin;
    }
}
