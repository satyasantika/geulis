<?php

namespace App\Services\Simulasi;

use App\Enums\Peran;
use App\Models\Classroom;
use App\Models\School;
use App\Models\SimulationLoginToken;
use App\Models\SimulationRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Membuat satu sesi simulasi: sekolah sementara, guru, kelas, siswa, dan
 * token QR berurutan. Kelas `non_riset` supaya tidak masuk papan kelengkapan.
 */
final class PembuatSimulasi
{
    public const int MAKS_KELAS = 12;

    public const int MAKS_GURU = 12;

    public const int MAKS_SISWA = 80;

    public function buat(int $jumlahKelas, int $jumlahGuru, int $jumlahSiswa, User $admin): SimulationRun
    {
        $jumlahKelas = max(1, min(self::MAKS_KELAS, $jumlahKelas));
        $jumlahGuru = max(1, min(self::MAKS_GURU, $jumlahGuru));
        $jumlahSiswa = max(1, min(self::MAKS_SISWA, $jumlahSiswa));

        return DB::transaction(function () use ($jumlahKelas, $jumlahGuru, $jumlahSiswa, $admin): SimulationRun {
            $run = SimulationRun::query()->create([
                'created_by' => $admin->getKey(),
                'jumlah_kelas' => $jumlahKelas,
                'jumlah_guru' => $jumlahGuru,
                'jumlah_siswa' => $jumlahSiswa,
                'layar_token' => Str::lower(Str::random(40)),
            ]);

            $sekolah = School::query()->create([
                'nama' => 'Sekolah Simulasi #'.$run->getKey(),
                'kabupaten_kota' => 'Lainnya',
                'npsn' => null,
            ]);
            $run->forceFill(['school_id' => $sekolah->getKey()])->save();

            $tahun = $this->tahunAjaran();
            $guru = [];
            for ($i = 1; $i <= $jumlahGuru; $i++) {
                $guru[] = $this->akun($run, $sekolah, Peran::Guru, 'Guru Simulasi '.$i, 'sim'.$run->getKey().'g'.$i);
            }

            $kelas = [];
            for ($i = 1; $i <= $jumlahKelas; $i++) {
                $pengampu = $guru[($i - 1) % count($guru)];
                $kelas[] = Classroom::query()->create([
                    'school_id' => $sekolah->getKey(),
                    'guru_id' => $pengampu->getKey(),
                    'simulation_run_id' => $run->getKey(),
                    'nama' => 'Sim '.$i,
                    'tahun_ajaran' => $tahun,
                    'kelompok_riset' => 'non_riset',
                ]);
            }

            $siswa = [];
            for ($i = 1; $i <= $jumlahSiswa; $i++) {
                $rombel = $kelas[($i - 1) % count($kelas)];
                $akun = $this->akun($run, $sekolah, Peran::Siswa, 'Siswa Simulasi '.$i, 'sim'.$run->getKey().'s'.$i);
                $akun->consent()->create([
                    'setuju_data_penelitian' => false,
                    'persetujuan_ortu_diterima' => false,
                    'disetujui_pada' => now(),
                ]);
                $rombel->enrollments()->create(['user_id' => $akun->getKey()]);
                $siswa[] = $akun;
            }

            $urutan = 1;
            foreach ([...$guru, ...$siswa] as $pengguna) {
                SimulationLoginToken::query()->create([
                    'simulation_run_id' => $run->getKey(),
                    'user_id' => $pengguna->getKey(),
                    'token' => Str::lower(Str::random(40)),
                    'urutan' => $urutan,
                ]);
                $urutan++;
            }

            return $run->fresh(['tokens.user', 'classrooms', 'school']) ?? $run;
        });
    }

    private function akun(SimulationRun $run, School $sekolah, Peran $peran, string $nama, string $username): User
    {
        $pengguna = User::query()->create([
            'nama' => $nama,
            'username' => $username,
            'password' => Str::password(16),
            'school_id' => $sekolah->getKey(),
            'simulation_run_id' => $run->getKey(),
            'kode_anonim' => $peran === Peran::Siswa ? User::kodeAnonimBerikutnya('T') : null,
            'aktif' => true,
        ]);
        $pengguna->berikanPeran($peran);

        return $pengguna;
    }

    private function tahunAjaran(): string
    {
        $tahun = (int) now()->format('Y');

        return now()->month >= 7 ? "{$tahun}/".($tahun + 1) : ($tahun - 1)."/{$tahun}";
    }
}
