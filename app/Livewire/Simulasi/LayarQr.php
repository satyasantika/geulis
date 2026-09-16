<?php

namespace App\Livewire\Simulasi;

use App\Models\SimulationLoginToken;
use App\Models\SimulationRun;
use App\Services\Simulasi\QrSvg;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Layar proyektor: satu QR pada satu waktu. Setelah pemiliknya masuk,
 * token berikutnya muncul. Dilindungi layar_token, bukan akun.
 */
#[Layout('components.layouts.simulasi')]
class LayarQr extends Component
{
    public int $runId;

    public function mount(string $layarToken): void
    {
        $run = SimulationRun::query()->where('layar_token', $layarToken)->firstOrFail();
        $this->runId = $run->getKey();
    }

    public function render(QrSvg $qr): View
    {
        $run = SimulationRun::query()->with(['tokens.user.roles'])->findOrFail($this->runId);
        $berikut = $run->tokenBerikutnya();
        $total = $run->jumlah_guru + $run->jumlah_siswa;
        $sudah = $run->tokens->whereNotNull('diklaim_pada')->count();

        return view('livewire.simulasi.layar-qr', [
            'run' => $run,
            'token' => $berikut,
            'qrSvg' => $berikut instanceof SimulationLoginToken ? $qr->dariUrl($berikut->tautanMasuk()) : null,
            'sudah' => $sudah,
            'total' => $total,
        ]);
    }
}
