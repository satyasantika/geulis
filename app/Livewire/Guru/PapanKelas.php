<?php

namespace App\Livewire\Guru;

use App\Models\Classroom;
use App\Models\User;
use App\Services\Guru\PapanKelasService;
use App\Services\Guru\PengesampinganGuru;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * G-01 Papan kelas: siapa yang perlu didatangi hari ini. Peta panas M per
 * unit pemeriksaan + tombol "Ubah" (override level, alasan wajib).
 */
class PapanKelas extends Component
{
    #[Locked]
    public int $kelasId;

    public ?int $ubahSiswaId = null;

    public string $levelBaru = '';

    public string $alasan = '';

    public function mount(Classroom $classroom): void
    {
        abort_unless($classroom->guru_id === auth()->id(), 403);
        $this->kelasId = $classroom->getKey();
    }

    public function bukaUbah(int $siswaId, string $levelKini): void
    {
        $this->ubahSiswaId = $siswaId;
        $this->levelBaru = $levelKini;
        $this->alasan = '';
        $this->resetErrorBag();
    }

    public function batalUbah(): void
    {
        $this->ubahSiswaId = null;
    }

    public function simpanUbah(PengesampinganGuru $pengesampingan): void
    {
        $this->resetErrorBag('alasan');
        $siswa = User::query()->findOrFail($this->ubahSiswaId);

        try {
            $pengesampingan->jalankan(auth()->user(), $siswa, $this->levelBaru, $this->alasan);
        } catch (InvalidArgumentException $e) {
            $this->addError('alasan', $e->getMessage());

            return;
        }

        $this->ubahSiswaId = null;
        $this->alasan = '';
    }

    public function render(PapanKelasService $papan): View
    {
        $kelas = Classroom::query()->with('school')->findOrFail($this->kelasId);
        $data = $papan->data($kelas);

        return view('livewire.guru.papan-kelas', ['kelas' => $kelas, ...$data, 'labelLevel' => config('angket.label_level')])
            ->title('Papan '.$kelas->nama);
    }
}
