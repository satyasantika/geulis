<?php

namespace App\Livewire\Guru;

use App\Models\Meeting;
use App\Models\Product;
use App\Models\Rubric;
use App\Models\RubricScore;
use App\Services\Rubrik\PenskorRubrik;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** G-03 Penilaian produk dengan rubrik analitik empat tingkat. */
class PenilaianRubrik extends Component
{
    #[Locked]
    public int $meetingId;

    /** @var array<int, array<int, int|null>> product_id => criteria_id => tingkat */
    public array $tingkat = [];

    /** @var array<int, array<int, string>> */
    public array $umpanBalik = [];

    public function mount(Meeting $meeting): void
    {
        $this->meetingId = $meeting->getKey();

        foreach ($this->produk() as $p) {
            foreach ($p->rubricScores as $s) {
                $this->tingkat[$p->id][$s->rubric_criteria_id] = $s->tingkat;
                $this->umpanBalik[$p->id][$s->rubric_criteria_id] = $s->umpan_balik ?? '';
            }
        }
    }

    public function simpan(int $produkId): void
    {
        $produk = $this->produk()->firstWhere('id', $produkId);
        abort_if($produk === null, 403);

        $this->resetErrorBag("tingkat.{$produkId}");
        $rubrik = $this->rubrik();

        foreach ($rubrik->criteria as $k) {
            $t = $this->tingkat[$produkId][$k->id] ?? null;
            if ($t === null || $t === '' || (int) $t < 1 || (int) $t > 4) {
                $this->addError("tingkat.{$produkId}", 'Isi keempat kriteria (tingkat 1–4).');

                return;
            }
        }

        foreach ($rubrik->criteria as $k) {
            RubricScore::query()->updateOrCreate(
                ['product_id' => $produkId, 'rubric_criteria_id' => $k->id],
                ['penilai_id' => auth()->id(), 'tingkat' => (int) $this->tingkat[$produkId][$k->id], 'umpan_balik' => $this->umpanBalik[$produkId][$k->id] ?? null],
            );
        }
    }

    public function rubrik(): Rubric
    {
        return Rubric::query()->with('criteria')->where('untuk', 'produk_akhir')->firstOrFail();
    }

    /** @return Collection<int, Product> */
    public function produk(): Collection
    {
        $idSiswa = auth()->user()->kelasDiampu()->with('enrollments')->get()->flatMap(fn ($k) => $k->enrollments->pluck('user_id'))->unique();

        return Product::query()->with(['user', 'rubricScores'])
            ->where('meeting_id', $this->meetingId)->whereIn('user_id', $idSiswa)
            ->orderBy('dikirim_pada')->get();
    }

    public function render(PenskorRubrik $penskor): View
    {
        $rubrik = $this->rubrik();
        $produk = $this->produk();

        $skor = $produk->mapWithKeys(fn (Product $p) => [$p->id => $penskor->skor(
            $rubrik->criteria->map(fn ($k) => ['bobot' => $k->bobot, 'tingkat' => $p->rubricScores->firstWhere('rubric_criteria_id', $k->id)?->tingkat])->all()
        )]);

        return view('livewire.guru.penilaian-rubrik', [
            'meeting' => Meeting::query()->findOrFail($this->meetingId),
            'rubrik' => $rubrik,
            'produk' => $produk,
            'skor' => $skor,
        ])->title('Penilaian Produk');
    }
}
