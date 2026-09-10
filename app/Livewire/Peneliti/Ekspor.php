<?php

namespace App\Livewire\Peneliti;

use App\Models\DataExport;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Title;
use Livewire\Component;

/** P-03 Ekspor .xlsx (selalu kode anonim) + riwayat. */
#[Title('Ekspor Data')]
class Ekspor extends Component
{
    public ?string $pesan = null;

    public function jalankan(): void
    {
        $kode = Artisan::call('geulis:ekspor', ['--oleh' => auth()->id()]);
        $this->pesan = $kode === 0 ? 'Ekspor selesai. Unduh dari daftar di bawah.' : 'Ekspor gagal: '.trim(Artisan::output());
    }

    public function render(): View
    {
        return view('livewire.peneliti.ekspor', [
            'riwayat' => DataExport::query()->with('pembuat')->latest()->limit(20)->get(),
            'anonim' => (bool) config('geulis.anonimkan_ekspor'),
        ]);
    }
}
