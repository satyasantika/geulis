<?php

namespace App\Livewire\Siswa;

use App\Models\Meeting;
use App\Models\Product;
use App\Models\Rubric;
use App\Services\Konten\KemajuanSiswa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * S-10 Unggah produk (diferensiasi produk — bentuk dipilih siswa).
 * Rubrik ditampilkan SEBELUM mengerjakan. Batas 2 MB per berkas (batas
 * unggah PHP di server sekolah), kuota 10 MB per siswa; video lewat tautan.
 */
class Produk extends Component
{
    use WithFileUploads;

    public const int MAKS_KB = 2048;

    public const int KUOTA_KB = 10240;

    #[Locked]
    public int $meetingId;

    public string $bentuk = '';

    public ?TemporaryUploadedFile $berkas = null;

    public string $tautan = '';

    public string $deskripsi = '';

    public function mount(Meeting $meeting, KemajuanSiswa $kemajuan): void
    {
        if (! $kemajuan->pertemuanTerbuka(auth()->user(), $meeting)) {
            session()->flash('pesan', 'Pertemuan ini belum terbuka.');
            $this->redirectRoute('siswa.jalur', navigate: true);

            return;
        }

        $this->meetingId = $meeting->getKey();

        $ada = $this->produk();
        if ($ada !== null) {
            $this->bentuk = $ada->bentuk;
            $this->tautan = $ada->tautan ?? '';
            $this->deskripsi = $ada->deskripsi ?? '';
        }
    }

    public function kirim(): void
    {
        $this->validate([
            'bentuk' => 'required|in:'.implode(',', array_keys(Product::BENTUK)),
            'berkas' => 'nullable|file|mimes:png,jpg,jpeg,webp,pdf,svg|max:'.self::MAKS_KB,
            'tautan' => 'nullable|url|max:500',
            'deskripsi' => 'required|string|min:20|max:3000',
        ], [
            'bentuk.required' => 'Pilih satu bentuk produk dulu.',
            'deskripsi.min' => 'Ceritakan karyamu setidaknya 20 karakter — algoritma dan artefak yang dipakai.',
            'berkas.max' => 'Berkas maksimal 2 MB. Perkecil gambar atau unggah PDF.',
        ]);

        if ($this->berkas === null && $this->tautan === '' && $this->produk()?->berkas === null) {
            $this->addError('berkas', 'Unggah berkas atau isi tautan (untuk video).');

            return;
        }

        $siswa = auth()->user();
        $path = $this->produk()?->berkas;

        if ($this->berkas !== null) {
            $terpakai = $this->kuotaTerpakaiKb($siswa->getKey());
            if ($terpakai + (int) ceil($this->berkas->getSize() / 1024) > self::KUOTA_KB) {
                $this->addError('berkas', 'Kuota unggahmu (10 MB) sudah habis. Hapus atau perkecil berkas lain dulu.');

                return;
            }
            if ($path !== null) {
                Storage::disk('local')->delete($path);
            }
            $path = $this->berkas->storeAs('produk-siswa/'.$siswa->getKey(), now()->format('YmdHis').'-'.$this->berkas->getClientOriginalName(), 'local');
        }

        Product::query()->updateOrCreate(
            ['user_id' => $siswa->getKey(), 'meeting_id' => $this->meetingId],
            ['bentuk' => $this->bentuk, 'berkas' => $path, 'tautan' => $this->tautan ?: null, 'deskripsi' => $this->deskripsi, 'dikirim_pada' => now()],
        );

        $this->berkas = null;
        session()->flash('pesan', 'Karyamu terkirim. Gurumu akan menilainya dengan rubrik di atas.');
    }

    public function produk(): ?Product
    {
        return Product::query()->where('user_id', auth()->id())->where('meeting_id', $this->meetingId)->first();
    }

    protected function kuotaTerpakaiKb(int $userId): int
    {
        $total = 0;
        foreach (Storage::disk('local')->files('produk-siswa/'.$userId) as $f) {
            $total += Storage::disk('local')->size($f);
        }

        return (int) ceil($total / 1024);
    }

    public function render(): View
    {
        return view('livewire.siswa.produk', [
            'meeting' => Meeting::query()->findOrFail($this->meetingId),
            'rubrik' => Rubric::query()->with('criteria')->where('untuk', 'produk_akhir')->first(),
            'produk' => $this->produk(),
            'daftarBentuk' => Product::BENTUK,
        ])->title('Produk Akhir');
    }
}
