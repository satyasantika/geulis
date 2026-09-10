<?php

namespace App\Livewire\Guru;

use App\Models\CtResponse;
use App\Services\Asesmen\PencatatTesCt;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Antrean penilaian uraian tes CT untuk siswa di kelas yang diampu guru.
 * Rubrik butir 0–4 ditampilkan di samping jawaban.
 */
#[Title('Penilaian Uraian')]
class PenilaianUraian extends Component
{
    /** @var array<int, int|null> response_id => skor */
    public array $skor = [];

    /** @var array<int, string> */
    public array $catatan = [];

    public function nilai(int $responsId, PencatatTesCt $pencatat): void
    {
        $respons = $this->antrean()->firstWhere('id', $responsId);
        abort_if($respons === null, 403);

        $this->resetErrorBag("skor.{$responsId}");
        $nilai = $this->skor[$responsId] ?? null;
        if ($nilai === null || $nilai === '') {
            $this->addError("skor.{$responsId}", 'Pilih tingkat 0–4.');

            return;
        }

        $pencatat->nilaiUraian($respons, auth()->user(), (float) $nilai, $this->catatan[$responsId] ?? null);
        unset($this->skor[$responsId], $this->catatan[$responsId]);
    }

    /** @return Collection<int, CtResponse> */
    public function antrean(): Collection
    {
        $idSiswa = auth()->user()->kelasDiampu()->with('enrollments')->get()->flatMap(fn ($k) => $k->enrollments->pluck('user_id'))->unique();

        return CtResponse::query()
            ->with(['item.test', 'user'])
            ->whereIn('user_id', $idSiswa)
            ->whereNull('skor_manual')
            ->whereHas('item', fn ($q) => $q->where('tipe', 'uraian'))
            ->whereHas('user.ctScores', fn ($q) => $q->whereNotNull('dikumpulkan_pada')->whereColumn('ct_scores.ct_test_id', 'ct_items.ct_test_id'))
            ->join('ct_items', 'ct_items.id', '=', 'ct_responses.ct_item_id')
            ->select('ct_responses.*')
            ->orderBy('ct_items.ct_test_id')->orderBy('ct_items.urutan')->orderBy('ct_responses.user_id')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.guru.penilaian-uraian', ['antrean' => $this->antrean()]);
    }
}
