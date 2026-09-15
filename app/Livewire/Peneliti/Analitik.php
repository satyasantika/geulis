<?php

namespace App\Livewire\Peneliti;

use App\Models\CtItem;
use App\Models\Questionnaire;
use App\Services\Research\AnalitikPenelitian;
use App\Services\Research\PenghitungKepraktisan;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/** P-02 Analitik: N-Gain per kelompok, kelas, indikator; kepraktisan angket. */
#[Title('Analitik Penelitian')]
class Analitik extends Component
{
    public function render(AnalitikPenelitian $analitik, PenghitungKepraktisan $kepraktisan): View
    {
        $baris = $analitik->perSiswa();

        $angket = [];
        foreach (Questionnaire::query()->where('jenis', 'kepraktisan')->with('items.responses')->get() as $q) {
            $angket[$q->sasaran] = (new PenghitungKepraktisan($q->skala_maks, config('angket.kepraktisan')))->rekap(
                $q->items->map(fn ($i) => ['aspek' => $i->aspek, 'butir_negatif' => $i->butir_negatif, 'skor' => $i->responses->pluck('skor')->all()])->all()
            );
        }

        return view('livewire.peneliti.analitik', [
            'baris' => $baris,
            'kelompok' => $analitik->perKelompok($baris),
            'kelas' => $analitik->perKelas($baris),
            'indikator' => $analitik->perIndikator($baris),
            'angket' => $angket,
            'labelIndikator' => CtItem::INDIKATOR,
        ]);
    }
}
