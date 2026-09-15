<?php

namespace App\Livewire\Angket;

use App\Models\Questionnaire;
use App\Models\QuestionnaireReflection;
use App\Models\QuestionnaireResponse;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Angket persepsi (30 butir, 9 aspek, skala 1-5) untuk siswa. Berbeda dari
 * AngketRespons (kepraktisan) -- instrumen terpisah, tidak terikat status
 * pertemuan, diaktifkan manual oleh peneliti lewat kolom `aktif`.
 */
class AngketPersepsi extends Component
{
    #[Locked]
    public int $angketId;

    /** @var array<int, int|null> */
    public array $jawaban = [];

    /** @var array<string, string> */
    public array $catatan = ['disukai' => '', 'diperbaiki' => '', 'saran' => ''];

    public bool $selesai = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->punyaPeran('siswa'), 403);

        $angket = Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'persepsi')->where('aktif', true)->firstOrFail();

        $this->angketId = $angket->getKey();
        $this->jawaban = auth()->user()->questionnaireResponses()
            ->whereIn('questionnaire_item_id', $angket->items()->pluck('id'))
            ->pluck('skor', 'questionnaire_item_id')->all();
        $this->catatan = QuestionnaireReflection::query()
            ->where('user_id', auth()->id())->where('questionnaire_id', $angket->id)
            ->pluck('jawaban', 'kode')->union(collect($this->catatan))->all();
        $this->selesai = count($this->jawaban) >= $angket->items()->count();
    }

    public function updated(string $nama): void
    {
        if (! preg_match('/^jawaban\.(\d+)$/', $nama, $m)) {
            return;
        }

        $itemId = (int) $m[1];
        $skor = (int) ($this->jawaban[$itemId] ?? 0);
        if ($skor < 1) {
            return;
        }

        QuestionnaireResponse::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'questionnaire_item_id' => $itemId],
            ['skor' => $skor],
        );
    }

    public function kirim(): void
    {
        $angket = $this->angket();
        $kurang = $angket->items->filter(fn ($i) => empty($this->jawaban[$i->id]))->count();
        if ($kurang > 0) {
            $this->addError('kirim', "Masih ada {$kurang} pernyataan yang belum dijawab.");

            return;
        }

        foreach ($this->catatan as $kode => $jawaban) {
            if (trim((string) $jawaban) === '') {
                continue;
            }

            QuestionnaireReflection::query()->updateOrCreate(
                ['user_id' => auth()->id(), 'questionnaire_id' => $angket->id, 'kode' => $kode],
                ['jawaban' => $jawaban],
            );
        }

        $this->selesai = true;
    }

    public function angket(): Questionnaire
    {
        return Questionnaire::query()->with('items')->findOrFail($this->angketId);
    }

    public function render(): View
    {
        $angket = $this->angket();

        return view('livewire.angket.angket-persepsi', [
            'angket' => $angket,
            'skala' => config('angket.skala5'),
            'catatanLabel' => config('angket.catatan_persepsi'),
            'kembali' => auth()->user()->rutePulang(),
        ])->title($angket->nama);
    }
}
