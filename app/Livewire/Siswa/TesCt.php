<?php

namespace App\Livewire\Siswa;

use App\Models\CtItem;
use App\Models\CtScore;
use App\Models\CtTest;
use App\Services\Asesmen\PencatatTesCt;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * S-11 Tes CT pra/pasca. Pewaktu berjalan di klien dari `sisaDetik` server;
 * setiap jawaban disimpan ke server DAN ke localStorage (Alpine) sehingga
 * putus koneksi tidak menghilangkan jawaban: saat daring kembali, antrean
 * dikirim ulang; saat kumpul, seluruh jawaban dikirim sekaligus.
 */
class TesCt extends Component
{
    #[Locked]
    public int $tesId;

    /** @var array<int, string|null> ct_item_id => jawaban tersimpan di server */
    public array $jawaban = [];

    public bool $selesai = false;

    public function mount(CtTest $ctTest, PencatatTesCt $pencatat): void
    {
        abort_unless($ctTest->aktif, 404);

        $this->tesId = $ctTest->getKey();
        $skor = $pencatat->mulai(auth()->user(), $ctTest);
        $this->selesai = $skor->dikumpulkan_pada !== null;

        $this->jawaban = auth()->user()->ctResponses()
            ->whereIn('ct_item_id', $ctTest->items()->pluck('id'))
            ->pluck('jawaban', 'ct_item_id')
            ->all();
    }

    /** Autosave satu butir (dipanggil Alpine, boleh gagal saat luring). */
    public function simpanJawaban(int $butirId, ?string $jawaban, PencatatTesCt $pencatat): bool
    {
        $skor = $this->skor();
        if (! $pencatat->masihBoleh($skor)) {
            return false;
        }

        $butir = CtItem::query()->where('ct_test_id', $this->tesId)->findOrFail($butirId);
        $pencatat->simpanJawaban(auth()->user(), $butir, $jawaban);
        $this->jawaban[$butirId] = $jawaban;

        return true;
    }

    /**
     * @param  array<int|string, string|null>  $semua  seluruh jawaban dari klien (localStorage menang)
     */
    public function kumpulkan(array $semua, PencatatTesCt $pencatat): void
    {
        $skor = $this->skor();
        if ($skor->dikumpulkan_pada !== null) {
            $this->selesai = true;

            return;
        }

        $bersih = [];
        foreach ($semua as $id => $j) {
            $bersih[(int) $id] = is_string($j) && trim($j) !== '' ? mb_substr($j, 0, 4000) : null;
        }

        $pencatat->kumpulkan(auth()->user(), $this->tes(), $bersih + $this->jawaban);
        $this->selesai = true;
    }

    public function tes(): CtTest
    {
        return CtTest::query()->with('items')->findOrFail($this->tesId);
    }

    protected function skor(): CtScore
    {
        return CtScore::query()->with('test')->where('user_id', auth()->id())->where('ct_test_id', $this->tesId)->firstOrFail();
    }

    public function render(PencatatTesCt $pencatat): View
    {
        $tes = $this->tes();
        $skor = $this->skor();

        return view('livewire.siswa.tes-ct', [
            'tes' => $tes,
            'butir' => $tes->items,
            'sisaDetik' => $this->selesai ? 0 : $pencatat->sisaDetik($skor),
            'kunciLokal' => 'geulis-tes-'.$tes->getKey().'-'.auth()->id(),
            'indikator' => CtItem::INDIKATOR,
        ])->title($tes->judul);
    }
}
