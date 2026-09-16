<?php

namespace App\Services\Simulasi;

use App\Models\Classroom;
use App\Models\School;
use App\Models\SimulationRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Menghapus sesi simulasi beserta akun dan kelasnya.
 *
 * Jejak belajar akun simulasi ikut terhapus lewat FK (termasuk baris
 * adaptation_logs milik akun T-xxx). Itu disengaja: simulasi bukan bukti
 * empiris penelitian. Data kelas riset sungguhan tidak disentuh.
 */
final class PenghapusSimulasi
{
    public function hapus(SimulationRun $run): void
    {
        DB::transaction(function () use ($run): void {
            $sekolahId = $run->school_id;

            $run->tokens()->delete();
            Classroom::query()->where('simulation_run_id', $run->getKey())->update(['guru_id' => null]);
            User::query()->where('simulation_run_id', $run->getKey())->delete();
            Classroom::query()->where('simulation_run_id', $run->getKey())->delete();
            $run->delete();

            if ($sekolahId !== null) {
                School::query()->whereKey($sekolahId)->delete();
            }
        });
    }
}
