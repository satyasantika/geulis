<?php

namespace App\Livewire\Guru;

use App\Models\Classroom;
use App\Models\School;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * G-00 Beranda guru: daftar kelas yang diampu + formulir kelas baru.
 */
#[Title('Beranda Guru')]
class Beranda extends Component
{
    #[Validate('required|string|max:50')]
    public string $nama = '';

    #[Validate('required|string|max:12|regex:/^\d{4}\/\d{4}$/')]
    public string $tahun_ajaran = '';

    #[Validate('required|exists:schools,id')]
    public ?int $school_id = null;

    public bool $formTerbuka = false;

    public function mount(): void
    {
        $tahun = (int) now()->format('Y');
        $this->tahun_ajaran = now()->month >= 7 ? "{$tahun}/".($tahun + 1) : ($tahun - 1)."/{$tahun}";
        $this->school_id = auth()->user()->school_id;
    }

    public function simpan(): void
    {
        $this->validate();

        Classroom::query()->create([
            'school_id' => $this->school_id,
            'guru_id' => auth()->id(),
            'nama' => $this->nama,
            'tahun_ajaran' => $this->tahun_ajaran,
        ]);

        $this->reset('nama', 'formTerbuka');
    }

    /** @return Collection<int, Classroom> */
    public function kelas(): Collection
    {
        return auth()->user()->kelasDiampu()
            ->withCount('enrollments')
            ->with('school')
            ->orderByDesc('aktif')
            ->orderBy('nama')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.guru.beranda', [
            'daftarKelas' => $this->kelas(),
            'sekolah' => School::query()->orderBy('nama')->get(),
        ]);
    }
}
