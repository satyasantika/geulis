<?php

namespace App\Livewire\Siswa;

use App\Models\CtItem;
use App\Models\LessonUnit;
use App\Services\Rubrik\PenskorRubrik;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/** S-09 Kemajuanku: empat kemampuan CT, penguasaan per unit, galeri karya. */
#[Title('Kemajuanku')]
class Kemajuan extends Component
{
    public function render(PenskorRubrik $penskorRubrik): View
    {
        $siswa = auth()->user();

        $skorCt = $siswa->ctScores()->with('test')->whereNotNull('dikumpulkan_pada')->get()
            ->sortBy(fn ($s) => $s->test->jenis === 'posttest' ? 1 : 0)->last();

        $ct = null;
        if ($skorCt !== null) {
            $maks = $skorCt->test->items->groupBy('indikator')->map(fn ($g) => $g->sum('skor_maks'));
            $ct = collect(CtItem::INDIKATOR)->map(fn ($label, $kode) => [
                'label' => $label,
                'persen' => ($maks[$kode] ?? 0) > 0 ? round($skorCt->{'skor_'.strtolower($kode)} / $maks[$kode] * 100) : null,
            ]);
        }

        $unitPemeriksaan = LessonUnit::query()->with('meeting')->where('tipe', 'pemeriksaan')->get()->sortBy(fn ($u) => $u->meeting->urutan)->values();
        $m = $siswa->masteryStates()->get()->keyBy('lesson_unit_id');
        $penguasaan = $unitPemeriksaan->map(fn (LessonUnit $u) => ['label' => 'P'.$u->meeting->urutan, 'm' => isset($m[$u->id]) ? (float) $m[$u->id]->nilai_m : null]);

        $motif = $siswa->motifSubmissions()->with('activity.lessonUnit.meeting')->orderBy('id')->get()
            ->groupBy('activity_id')->map(fn ($g) => $g->sortByDesc('skor_kemiripan')->first())->values();

        $produk = $siswa->products()->with(['meeting', 'rubricScores.criteria'])->get()->map(function ($p) use ($penskorRubrik) {
            $kriteria = $p->rubricScores->map(fn ($s) => ['bobot' => $s->criteria->bobot, 'tingkat' => $s->tingkat]);

            return ['produk' => $p, 'skor' => $kriteria->isEmpty() ? null : $penskorRubrik->skor($kriteria->all())['skor']];
        });

        return view('livewire.siswa.kemajuan', [
            'ct' => $ct,
            'jenisTes' => $skorCt?->test->jenis,
            'ctLengkap' => $skorCt?->dihitung_pada !== null,
            'penguasaan' => $penguasaan,
            'motif' => $motif,
            'produk' => $produk,
            'labelLevel' => config('angket.label_level'),
            'level' => $siswa->enrollmentAktif()?->level_kini,
        ]);
    }
}
