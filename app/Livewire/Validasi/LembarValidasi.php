<?php

namespace App\Livewire\Validasi;

use App\Models\ExpertValidation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * V-01 Lembar validasi ahli. Dibuka lewat tautan bertanda (URL::signedRoute)
 * berisi token undangan — validator tidak perlu membuat akun. Jawaban disimpan
 * per butir; boleh ditutup dan dilanjutkan lewat tautan yang sama.
 */
class LembarValidasi extends Component
{
    #[Locked]
    public int $validasiId;

    /** @var array<int, int|null> item_id => skor */
    public array $skor = [];

    /** @var array<int, string> */
    public array $saran = [];

    public int $aspekKe = 0;

    public function mount(string $token): void
    {
        $validasi = ExpertValidation::query()->with('instrument.items')->where('token', $token)->firstOrFail();

        if ($validasi->status === 'dikirim') {
            $validasi->update(['status' => 'dibuka']);
        }

        $this->validasiId = $validasi->getKey();
        foreach ($validasi->ratings as $r) {
            $this->skor[$r->validation_item_id] = $r->skor;
            $this->saran[$r->validation_item_id] = $r->saran ?? '';
        }
    }

    public function updated(string $nama): void
    {
        if (! preg_match('/^(skor|saran)\.(\d+)$/', $nama, $m)) {
            return;
        }
        $itemId = (int) $m[2];
        if (! isset($this->skor[$itemId]) || $this->skor[$itemId] === null || $this->skor[$itemId] === '') {
            return;
        }

        $this->validasi()->ratings()->updateOrCreate(
            ['validation_item_id' => $itemId],
            ['skor' => (int) $this->skor[$itemId], 'saran' => $this->saran[$itemId] ?? null],
        );
    }

    public function aspekBerikutnya(): void
    {
        $this->aspekKe = min($this->aspekKe + 1, count($this->aspek()) - 1);
    }

    public function aspekSebelumnya(): void
    {
        $this->aspekKe = max(0, $this->aspekKe - 1);
    }

    public function kirim(): void
    {
        $validasi = $this->validasi();
        $items = $validasi->instrument->items;
        $terisi = $validasi->ratings()->count();

        if ($terisi < $items->count()) {
            $this->addError('kirim', sprintf('Masih ada %d butir yang belum diberi skor.', $items->count() - $terisi));

            return;
        }

        $validasi->update(['status' => 'selesai', 'selesai_pada' => now()]);
    }

    public function validasi(): ExpertValidation
    {
        return ExpertValidation::query()->with(['instrument.items', 'validator', 'ratings'])->findOrFail($this->validasiId);
    }

    /** @return list<string> */
    public function aspek(): array
    {
        return $this->validasi()->instrument->items->pluck('aspek')->unique()->values()->all();
    }

    public function render(): View
    {
        $validasi = $this->validasi();
        $aspek = $this->aspek();
        $namaAspek = $aspek[$this->aspekKe] ?? null;

        return view('livewire.validasi.lembar-validasi', [
            'validasi' => $validasi,
            'instrumen' => $validasi->instrument,
            'aspek' => $aspek,
            'namaAspek' => $namaAspek,
            'butir' => $validasi->instrument->items->where('aspek', $namaAspek)->values(),
            'terisi' => count(array_filter($this->skor, fn ($s) => $s !== null && $s !== '')),
            'total' => $validasi->instrument->items->count(),
            'selesai' => $validasi->status === 'selesai',
        ])->title('Lembar Validasi');
    }
}
