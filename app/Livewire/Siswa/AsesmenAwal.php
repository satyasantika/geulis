<?php

namespace App\Livewire\Siswa;

use App\Models\LearningProfile;
use App\Models\ReadinessItem;
use App\Models\User;
use App\Services\Differentiation\PlacementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * S-03 Asesmen awal: tes kesiapan (15) → angket profil (20) → angket minat (6)
 * → penempatan. Setiap jawaban langsung disimpan ke basis data, sehingga
 * siswa yang putus koneksi melanjutkan dari butir terakhir, bukan dari awal.
 */
#[Title('Asesmen Awal')]
class AsesmenAwal extends Component
{
    public const int BUTIR_PER_HALAMAN = 5;

    #[Locked]
    public string $tahap = 'kesiapan';

    /** Butir kesiapan yang sedang tampil. */
    #[Locked]
    public ?int $butirId = null;

    #[Locked]
    public int $mulaiButir = 0;

    public ?int $jawaban = null;

    /** @var array<int, int> */
    public array $jawabanProfil = [];

    /** @var array<int, int> */
    public array $jawabanMinat = [];

    public int $halaman = 0;

    public function mount(): void
    {
        if ($this->siswa()->placement !== null) {
            $this->redirectRoute('siswa.jalur', navigate: true);

            return;
        }

        $draf = $this->siswa()->learningProfile?->jawaban_mentah ?? [];
        $this->jawabanProfil = array_map('intval', $draf['profil'] ?? []);
        $this->jawabanMinat = array_map('intval', $draf['minat'] ?? []);

        $this->tentukanTahap();
    }

    // ---------- Tes kesiapan ----------

    public function jawabKesiapan(): void
    {
        $this->validate(['jawaban' => 'required|integer|min:0|max:3'], ['jawaban.required' => 'Pilih satu jawaban dulu, ya.']);

        $butir = ReadinessItem::query()->findOrFail($this->butirId);

        $this->siswa()->readinessResponses()->updateOrCreate(
            ['readiness_item_id' => $butir->getKey()],
            [
                'jawaban' => $this->jawaban,
                'benar' => $this->jawaban === $butir->kunci,
                'durasi_detik' => max(0, time() - $this->mulaiButir),
            ],
        );

        $this->jawaban = null;
        $this->tentukanTahap();
    }

    // ---------- Angket ----------

    public function updated(string $nama): void
    {
        if (str_starts_with($nama, 'jawabanProfil') || str_starts_with($nama, 'jawabanMinat')) {
            $this->simpanDraf();
        }
    }

    /** Profil 20 butir → 4 halaman; minat hanya 6 butir → satu halaman. */
    public function butirPerHalaman(): int
    {
        return $this->tahap === 'minat' ? count(config('angket.minat')) : self::BUTIR_PER_HALAMAN;
    }

    public function halamanBerikutnya(): void
    {
        $butir = $this->butirAngket();
        $perHalaman = $this->butirPerHalaman();
        $awal = $this->halaman * $perHalaman;
        $jawaban = $this->tahap === 'profil' ? $this->jawabanProfil : $this->jawabanMinat;

        for ($i = $awal; $i < min($awal + $perHalaman, count($butir)); $i++) {
            if (! isset($jawaban[$i])) {
                $this->addError('angket', 'Jawab semua pernyataan di halaman ini dulu, ya.');

                return;
            }
        }

        if ($awal + $perHalaman >= count($butir)) {
            $this->halaman = 0;
            $this->tentukanTahap();

            return;
        }

        $this->halaman++;
    }

    public function halamanSebelumnya(): void
    {
        $this->halaman = max(0, $this->halaman - 1);
    }

    public function selesai(PlacementService $penempatan): void
    {
        $this->tentukanTahap();

        if ($this->tahap !== 'selesai') {
            return;
        }

        $penempatan->tempatkan($this->siswa(), $this->jawabanProfil, $this->jawabanMinat);

        $this->redirectRoute('siswa.jalur', navigate: true);
    }

    // ---------- Pembantu ----------

    protected function tentukanTahap(): void
    {
        $butir = ReadinessItem::query()->orderBy('urutan')->get();
        $dijawab = $this->siswa()->readinessResponses()->pluck('readiness_item_id')->all();
        $berikut = $butir->first(fn (ReadinessItem $b) => ! in_array($b->getKey(), $dijawab, true));

        if ($berikut !== null) {
            $this->tahap = 'kesiapan';
            $this->butirId = $berikut->getKey();
            $this->mulaiButir = time();

            return;
        }

        if (count($this->jawabanProfil) < count(config('angket.profil'))) {
            $this->tahap = 'profil';

            return;
        }

        if (count($this->jawabanMinat) < count(config('angket.minat'))) {
            $this->tahap = 'minat';

            return;
        }

        $this->tahap = 'selesai';
    }

    protected function simpanDraf(): void
    {
        $this->resetErrorBag('angket');

        LearningProfile::query()->updateOrCreate(
            ['user_id' => $this->siswa()->getKey()],
            [
                'skor_visual' => 0, 'skor_simbolik' => 0, 'skor_naratif' => 0, 'modus_utama' => 'campuran',
                'jawaban_mentah' => ['profil' => $this->jawabanProfil, 'minat' => $this->jawabanMinat],
            ],
        );
    }

    /** @return list<array<string, string>> */
    public function butirAngket(): array
    {
        return $this->tahap === 'profil' ? config('angket.profil') : config('angket.minat');
    }

    protected function siswa(): User
    {
        return auth()->user();
    }

    public function render(): View
    {
        $butirKesiapan = $this->tahap === 'kesiapan' ? ReadinessItem::query()->find($this->butirId) : null;
        $totalKesiapan = ReadinessItem::query()->count();

        return view('livewire.siswa.asesmen-awal', [
            'butirKesiapan' => $butirKesiapan,
            'nomorKesiapan' => $butirKesiapan?->urutan,
            'totalKesiapan' => $totalKesiapan,
            'butirAngket' => Collection::make($this->butirAngket()),
            'skala' => config('angket.skala'),
        ]);
    }
}
