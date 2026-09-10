<?php

namespace App\Livewire\Siswa;

use App\Models\Activity;
use App\Models\MotifSubmission;
use App\Services\Konten\KemajuanSiswa;
use App\Services\Motif\PenilaiMotif;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * S-07 Motif Builder. Kanvas dan blok dikerjakan Alpine (motif-builder.js);
 * komponen ini menerima kiriman, menilai di server, dan menyimpan.
 */
class MotifBuilder extends Component
{
    #[Locked]
    public int $activityId;

    /** @var array<string, mixed>|null */
    public ?array $hasil = null;

    public ?string $galat = null;

    public function mount(Activity $activity, KemajuanSiswa $kemajuan): void
    {
        abort_unless($activity->tipe === 'motif_builder', 404);

        if (! $kemajuan->unitTerbuka(auth()->user(), $activity->lessonUnit)) {
            session()->flash('pesan', 'Bagian ini belum terbuka. Selesaikan bagian sebelumnya dulu, ya.');
            $this->redirectRoute('siswa.pertemuan', $activity->lessonUnit->meeting, navigate: true);

            return;
        }

        $this->activityId = $activity->getKey();
        $kemajuan->tandaiSedang(auth()->user(), $activity->lessonUnit);

        $terakhir = $this->kirimanTerakhir();
        if ($terakhir !== null) {
            $this->hasil = $this->ringkas($terakhir);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $perintah
     * @param  list<int>  $ditandai
     */
    public function kirim(array $perintah, array $ditandai, ?string $svg, PenilaiMotif $penilai, KemajuanSiswa $kemajuan): void
    {
        $aktivitas = $this->aktivitas();

        try {
            $kiriman = $penilai->nilai(auth()->user(), $aktivitas, $perintah, $ditandai, $svg);
        } catch (InvalidArgumentException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $this->galat = null;
        $this->hasil = $this->ringkas($kiriman);

        if ($kiriman->lolos) {
            $kemajuan->tandaiSelesai(auth()->user(), $aktivitas->lessonUnit);
        }
    }

    /** Setelah beberapa percobaan belum lolos, siswa boleh melanjutkan — kirimannya tetap tersimpan. */
    public function lanjutkan(KemajuanSiswa $kemajuan): void
    {
        $jumlah = auth()->user()->motifSubmissions()->where('activity_id', $this->activityId)->count();
        if ($jumlah < PenilaiMotif::MAKS_PERCOBAAN_SEBELUM_LANJUT) {
            return;
        }

        $kemajuan->tandaiSelesai(auth()->user(), $this->aktivitas()->lessonUnit);
        $this->redirectRoute('siswa.pertemuan', $this->aktivitas()->lessonUnit->meeting, navigate: true);
    }

    public function aktivitas(): Activity
    {
        return Activity::query()->with('lessonUnit.meeting')->findOrFail($this->activityId);
    }

    protected function kirimanTerakhir(): ?MotifSubmission
    {
        return auth()->user()->motifSubmissions()->where('activity_id', $this->activityId)->latest('id')->first();
    }

    /** @return array<string, mixed> */
    protected function ringkas(MotifSubmission $k): array
    {
        return [
            'kemiripan' => $k->skor_kemiripan,
            'lolos' => $k->lolos,
            'efisiensi' => $k->efisiensi,
            'langkah_siswa' => $k->langkah_siswa,
            'langkah_minimum' => $k->langkah_minimum,
            'percobaan_ke' => $k->percobaan_ke,
            'urutan_perintah' => $k->urutan_perintah,
            'motif_dasar_ditandai' => $k->motif_dasar_ditandai,
        ];
    }

    public function render(KemajuanSiswa $kemajuan): View
    {
        $aktivitas = $this->aktivitas();
        $jumlah = auth()->user()->motifSubmissions()->where('activity_id', $this->activityId)->count();

        return view('livewire.siswa.motif-builder', [
            'aktivitas' => $aktivitas,
            'unit' => $aktivitas->lessonUnit,
            'meeting' => $aktivitas->lessonUnit->meeting,
            'konfigurasi' => $aktivitas->konfigurasi + ['resolusi' => (int) config('geulis.motif_resolusi', 200)],
            'ambang' => (float) config('geulis.motif_ambang_lolos', 90.0),
            'selesai' => $kemajuan->unitSelesai(auth()->user(), $aktivitas->lessonUnit),
            'bolehLanjut' => $jumlah >= PenilaiMotif::MAKS_PERCOBAAN_SEBELUM_LANJUT,
            'jumlahPercobaan' => $jumlah,
        ])->title('Motif Builder');
    }
}
