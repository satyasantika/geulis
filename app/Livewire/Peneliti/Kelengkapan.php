<?php

namespace App\Livewire\Peneliti;

use App\Services\Research\KelengkapanData;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/** P-01 Papan kelengkapan data per kelas. */
#[Title('Kelengkapan Data')]
class Kelengkapan extends Component
{
    public function render(KelengkapanData $kelengkapan): View
    {
        $baris = $kelengkapan->perKelas();

        return view('livewire.peneliti.kelengkapan', [
            'baris' => $baris,
            'totalSiswa' => $baris->sum('n'),
            'totalKelas' => $baris->count(),
            'totalSekolah' => $baris->pluck('kelas.school_id')->unique()->count(),
        ]);
    }
}
