<?php

namespace App\Livewire\Angket;

use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Angket respons (kepraktisan) untuk siswa dan guru. Muncul otomatis setelah
 * pertemuan terakhir (siswa) atau kapan pun angket diaktifkan (guru).
 * Jawaban disimpan per butir.
 */
class AngketRespons extends Component
{
    #[Locked]
    public int $angketId;

    /** @var array<int, int|null> */
    public array $jawaban = [];

    public bool $selesai = false;

    public function mount(string $sasaran): void
    {
        $angket = Questionnaire::query()->where('sasaran', $sasaran)->where('aktif', true)->firstOrFail();
        abort_unless(auth()->user()->punyaPeran($sasaran), 403);

        $this->angketId = $angket->getKey();
        $this->jawaban = auth()->user()->questionnaireResponses()
            ->whereIn('questionnaire_item_id', $angket->items()->pluck('id'))
            ->pluck('skor', 'questionnaire_item_id')->all();
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

        $this->selesai = true;
    }

    public function angket(): Questionnaire
    {
        return Questionnaire::query()->with('items')->findOrFail($this->angketId);
    }

    public function render(): View
    {
        $angket = $this->angket();

        return view('livewire.angket.angket-respons', [
            'angket' => $angket,
            'skala' => $angket->skala_maks === 4 ? config('angket.skala') : array_combine(range(1, $angket->skala_maks), range(1, $angket->skala_maks)),
            'kembali' => auth()->user()->rutePulang(),
        ])->title($angket->nama);
    }
}
