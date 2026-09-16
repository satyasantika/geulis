<?php

namespace App\Livewire\Guru;

use App\Models\Classroom;
use App\Services\Kelas\PembacaCsvSiswa;
use App\Services\Kelas\PembacaTeksSiswa;
use App\Services\Kelas\PendaftarSiswa;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Detail kelas untuk guru: daftar siswa, impor copas/CSV, tambah satu siswa,
 * atur ulang PIN, dan tautan cetak kartu (G-05).
 */
class DetailKelas extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $kelasId;

    #[Validate('nullable|file|mimes:csv,txt|max:512')]
    public ?TemporaryUploadedFile $berkas = null;

    public string $namaBaru = '';

    public string $nisBaru = '';

    public string $jkBaru = '';

    #[Validate('nullable|string|max:20000')]
    public string $teksSiswa = '';

    /** @var list<string> */
    public array $pesan = [];

    /** @var list<string> */
    public array $galat = [];

    /** @var array<int, string> PIN yang baru diatur ulang, ditampilkan sekali. */
    public array $pinBaru = [];

    public function mount(Classroom $classroom): void
    {
        abort_unless($classroom->guru_id === auth()->id(), 403);

        $this->kelasId = $classroom->getKey();
    }

    public function impor(PembacaCsvSiswa $pembaca, PendaftarSiswa $pendaftar): void
    {
        $this->validate(['berkas' => 'required|file|mimes:csv,txt|max:512']);

        $hasil = $pembaca->baca($this->berkas->get());
        $this->galat = $hasil['galat'];

        if ($hasil['baris'] === []) {
            $this->galat[] = 'Tidak ada baris siswa yang bisa dibaca dari berkas.';
            $this->berkas = null;

            return;
        }

        $daftar = $pendaftar->daftarkanBanyak($this->kelas(), $hasil['baris']);
        $this->galat = [...$this->galat, ...$daftar['galat']];
        $this->pesan = [sprintf('%d siswa baru dibuat, %d sudah terdaftar sebelumnya.', $daftar['dibuat'], $daftar['sudah_ada'])];
        $this->berkas = null;
    }

    public function imporTeks(PembacaTeksSiswa $pembaca, PendaftarSiswa $pendaftar): void
    {
        $this->validate(['teksSiswa' => 'required|string|max:20000']);

        $hasil = $pembaca->baca($this->teksSiswa);
        $this->galat = $hasil['galat'];

        if ($hasil['baris'] === []) {
            $this->galat[] = 'Tidak ada baris siswa yang bisa dibaca dari daftar.';
            $this->teksSiswa = '';

            return;
        }

        $daftar = $pendaftar->daftarkanBanyak($this->kelas(), $hasil['baris']);
        $this->galat = [...$this->galat, ...$daftar['galat']];
        $this->pesan = [sprintf('%d siswa baru dibuat, %d sudah terdaftar sebelumnya.', $daftar['dibuat'], $daftar['sudah_ada'])];
        $this->teksSiswa = '';
    }

    public function tambahSatu(PendaftarSiswa $pendaftar): void
    {
        $data = $this->validate([
            'namaBaru' => 'required|string|max:255',
            'nisBaru' => 'required|digits_between:4,20',
            'jkBaru' => 'nullable|in:L,P',
        ]);

        $hasil = $pendaftar->daftarkanBanyak($this->kelas(), [[
            'nama' => $data['namaBaru'],
            'nis' => $data['nisBaru'],
            'jenis_kelamin' => $data['jkBaru'] ?: null,
        ]]);

        $this->galat = $hasil['galat'];
        $this->pesan = $hasil['galat'] === [] ? ['Siswa ditambahkan.'] : [];
        $this->reset('namaBaru', 'nisBaru', 'jkBaru');
    }

    public function aturUlangPin(int $siswaId, PendaftarSiswa $pendaftar): void
    {
        $siswa = $this->kelas()->siswa()->findOrFail($siswaId);

        $this->pinBaru[$siswaId] = $pendaftar->aturUlangPin($siswa);
    }

    public function keluarkan(int $siswaId): void
    {
        $this->kelas()->enrollments()->where('user_id', $siswaId)->update(['status' => 'keluar']);
    }

    public function kelas(): Classroom
    {
        return Classroom::query()->with('school')->findOrFail($this->kelasId);
    }

    public function render(): View
    {
        $kelas = $this->kelas();

        return view('livewire.guru.detail-kelas', [
            'kelas' => $kelas,
            'siswa' => $kelas->siswa()->wherePivot('status', 'aktif')->get(),
        ])->title($kelas->nama);
    }
}
