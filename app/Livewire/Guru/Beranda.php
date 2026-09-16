<?php

namespace App\Livewire\Guru;

use App\Models\Classroom;
use App\Models\School;
use App\Services\Kelas\PembacaTeksSiswa;
use App\Services\Kelas\PendaftarSiswa;
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

    #[Validate('nullable|string|max:20000')]
    public string $daftarSiswa = '';

    public bool $formTerbuka = false;

    /** @var list<string> */
    public array $pesan = [];

    /** @var list<string> */
    public array $galat = [];

    public function mount(): void
    {
        $tahun = (int) now()->format('Y');
        $this->tahun_ajaran = now()->month >= 7 ? "{$tahun}/".($tahun + 1) : ($tahun - 1)."/{$tahun}";
        $this->school_id = auth()->user()->school_id;
    }

    public function simpan(PembacaTeksSiswa $pembaca, PendaftarSiswa $pendaftar): void
    {
        $this->validate();

        $kelas = Classroom::query()->create([
            'school_id' => $this->school_id,
            'guru_id' => auth()->id(),
            'nama' => $this->nama,
            'tahun_ajaran' => $this->tahun_ajaran,
        ]);

        $this->pesan = [];
        $this->galat = [];

        if (trim($this->daftarSiswa) !== '') {
            $hasil = $pembaca->baca($this->daftarSiswa);
            $this->galat = $hasil['galat'];

            if ($hasil['baris'] !== []) {
                $daftar = $pendaftar->daftarkanBanyak($kelas, $hasil['baris']);
                $this->galat = [...$this->galat, ...$daftar['galat']];
                $this->pesan = [sprintf('%d siswa baru dibuat, %d sudah terdaftar sebelumnya.', $daftar['dibuat'], $daftar['sudah_ada'])];
            } elseif ($this->galat === []) {
                $this->galat[] = 'Tidak ada baris siswa yang bisa dibaca dari daftar.';
            }
        }

        $this->reset('nama', 'formTerbuka', 'daftarSiswa');
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
