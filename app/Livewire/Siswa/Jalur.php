<?php

namespace App\Livewire\Siswa;

use App\Models\CtTest;
use App\Services\Konten\KemajuanSiswa;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/** S-04 Beranda "Jalur Belajarmu": lima pertemuan beserta status kunci. */
#[Title('Jalur Belajarmu')]
class Jalur extends Component
{
    public function render(KemajuanSiswa $kemajuan): View
    {
        $siswa = auth()->user();

        return view('livewire.siswa.jalur', [
            'siswa' => $siswa,
            'penempatan' => $siswa->placement,
            'jalur' => $siswa->placement ? $kemajuan->jalur($siswa) : collect(),
            'tesAktif' => CtTest::query()->where('aktif', true)->orderBy('jenis', 'desc')->get()
                ->map(fn (CtTest $t) => ['tes' => $t, 'skor' => $siswa->ctScores()->where('ct_test_id', $t->id)->first()]),
            'labelLevel' => config('angket.label_level'),
            'labelModus' => config('angket.label_modus'),
            'labelArtefak' => config('angket.label_artefak'),
        ]);
    }
}
