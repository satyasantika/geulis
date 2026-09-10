<?php

namespace App\Livewire\Guru;

use App\Models\MasteryState;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * G-04 Daftar "perlu pendampingan" lintas kelas yang diampu: siswa yang
 * dieskalasi Mesin Diferensiasi (RULE_ESCALATE) dan belum ditindaklanjuti.
 */
#[Title('Perlu Pendampingan')]
class Pendampingan extends Component
{
    public function tandaiDitangani(int $masteryId): void
    {
        $status = $this->daftar()->firstWhere('id', $masteryId);
        abort_if($status === null, 403);

        // Menandai selesai didampingi TIDAK mengubah jejak adaptation_logs;
        // hanya status kini yang dilepas supaya daftar guru tetap bersih.
        $status->forceFill(['perlu_pendampingan' => false])->save();
    }

    /** @return Collection<int, MasteryState> */
    public function daftar(): Collection
    {
        $idSiswa = auth()->user()->kelasDiampu()->with('enrollments')->get()
            ->flatMap(fn ($k) => $k->enrollments->where('status', 'aktif')->pluck('user_id'))->unique();

        return MasteryState::query()
            ->with(['user', 'lessonUnit.meeting'])
            ->whereIn('user_id', $idSiswa)
            ->where('perlu_pendampingan', true)
            ->orderByDesc('iterasi_remedial')
            ->orderBy('updated_at')
            ->get();
    }

    public function render(): View
    {
        $daftar = $this->daftar();
        $polaUnit = $daftar->groupBy('lesson_unit_id')->filter(fn ($g) => $g->count() >= 3);

        return view('livewire.guru.pendampingan', ['daftar' => $daftar, 'polaUnit' => $polaUnit]);
    }
}
