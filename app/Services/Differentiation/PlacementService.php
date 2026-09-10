<?php

namespace App\Services\Differentiation;

use App\Models\Placement;
use App\Models\User;
use App\Services\Asesmen\PenskorAngket;
use App\Services\Asesmen\PenskorKesiapan;
use Illuminate\Support\Facades\DB;

/**
 * Lapisan penyimpanan penempatan awal: mengumpulkan R, P, B dari basis data,
 * memanggil DifferentiationEngine::tempatkan() (murni), lalu menyimpan hasil
 * beserta `penjelasan` ke `placements`, profil ke `learning_profiles`, dan
 * posisi awal ke `enrollments`.
 */
final class PlacementService
{
    public function __construct(
        private readonly DifferentiationEngine $engine,
        private readonly PenskorKesiapan $penskorKesiapan = new PenskorKesiapan,
        private readonly PenskorAngket $penskorAngket = new PenskorAngket,
    ) {}

    /**
     * @param  array<int, int>  $jawabanProfil  indeks butir => 1..4
     * @param  array<int, int>  $jawabanMinat
     */
    public function tempatkan(User $siswa, array $jawabanProfil, array $jawabanMinat): Placement
    {
        $jawabanKesiapan = $siswa->readinessResponses()
            ->with('item')
            ->get()
            ->map(fn ($r) => ['prasyarat' => $r->item->prasyarat, 'benar' => $r->benar])
            ->all();

        $kesiapan = $this->penskorKesiapan->skor($jawabanKesiapan);
        $profil = $this->penskorAngket->profil(config('angket.profil'), $jawabanProfil);
        $minat = $this->penskorAngket->minat(config('angket.minat'), $jawabanMinat);

        $hasil = $this->engine->tempatkan($kesiapan['skor'], $profil, $minat['pilihan']);
        $hasil['penjelasan']['kesiapan_per_prasyarat'] = $kesiapan['per_prasyarat'];
        $hasil['penjelasan']['minat'] = $minat['skor'];

        return DB::transaction(function () use ($siswa, $hasil, $profil, $minat, $kesiapan, $jawabanProfil, $jawabanMinat): Placement {
            $siswa->learningProfile()->updateOrCreate([], [
                'skor_visual' => $profil['visual'],
                'skor_simbolik' => $profil['simbolik'],
                'skor_naratif' => $profil['naratif'],
                'modus_utama' => $hasil['modus'],
                'artefak_pilihan' => $minat['pilihan'],
                'jawaban_mentah' => ['profil' => $jawabanProfil, 'minat' => $jawabanMinat],
            ]);

            $placement = $siswa->placement()->updateOrCreate([], [
                'skor_readiness' => $kesiapan['skor'],
                'level_awal' => $hasil['level_awal'],
                'modus' => $hasil['modus'],
                'artefak_utama' => $hasil['artefak_utama'],
                'penjelasan' => $hasil['penjelasan'],
            ]);

            $siswa->enrollments()->where('status', 'aktif')->update([
                'level_kini' => $hasil['level_awal'],
                'modus_kini' => $hasil['modus'],
            ]);

            return $placement;
        });
    }
}
