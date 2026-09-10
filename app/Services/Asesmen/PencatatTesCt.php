<?php

namespace App\Services\Asesmen;

use App\Models\CtItem;
use App\Models\CtResponse;
use App\Models\CtScore;
use App\Models\CtTest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Lapisan penyimpanan tes CT: mulai, simpan jawaban per butir (autosave),
 * kumpulkan, nilai uraian, dan hitung ulang rekap ct_scores.
 */
final class PencatatTesCt
{
    public const int TOLERANSI_DETIK = 90;

    public function __construct(private readonly PenskorTesCt $penskor = new PenskorTesCt) {}

    public function mulai(User $siswa, CtTest $tes): CtScore
    {
        return CtScore::query()->firstOrCreate(['user_id' => $siswa->getKey(), 'ct_test_id' => $tes->getKey()]);
    }

    public function sisaDetik(CtScore $skor): int
    {
        return max(0, $this->sisaDetikMentah($skor));
    }

    /** Boleh negatif: berapa detik sudah lewat dari batas. */
    private function sisaDetikMentah(CtScore $skor): int
    {
        $batas = $skor->created_at->copy()->addMinutes($skor->test->durasi_menit);

        return (int) floor(now()->diffInSeconds($batas, false));
    }

    public function masihBoleh(CtScore $skor): bool
    {
        return $skor->dikumpulkan_pada === null && $this->sisaDetikMentah($skor) + self::TOLERANSI_DETIK > 0;
    }

    public function simpanJawaban(User $siswa, CtItem $butir, ?string $jawaban, ?int $durasi = null): CtResponse
    {
        return CtResponse::query()->updateOrCreate(
            ['user_id' => $siswa->getKey(), 'ct_item_id' => $butir->getKey()],
            [
                'jawaban' => $jawaban,
                'skor_otomatis' => $this->penskor->skorOtomatis($butir->only(['tipe', 'kunci', 'skor_maks']), $jawaban),
                'durasi_detik' => $durasi,
            ],
        );
    }

    /**
     * @param  array<int, string|null>  $jawaban  ct_item_id => jawaban
     */
    public function kumpulkan(User $siswa, CtTest $tes, array $jawaban): CtScore
    {
        return DB::transaction(function () use ($siswa, $tes, $jawaban): CtScore {
            foreach ($tes->items as $butir) {
                if (array_key_exists($butir->getKey(), $jawaban)) {
                    $this->simpanJawaban($siswa, $butir, $jawaban[$butir->getKey()]);
                } else {
                    // Butir yang tak pernah tersentuh tetap dicatat kosong supaya rekap lengkap.
                    CtResponse::query()->firstOrCreate(
                        ['user_id' => $siswa->getKey(), 'ct_item_id' => $butir->getKey()],
                        ['jawaban' => null, 'skor_otomatis' => $this->penskor->skorOtomatis($butir->only(['tipe', 'kunci', 'skor_maks']), null)],
                    );
                }
            }

            $skor = $this->mulai($siswa, $tes);
            $skor->forceFill(['dikumpulkan_pada' => $skor->dikumpulkan_pada ?? now()])->save();

            return $this->hitungUlang($siswa, $tes);
        });
    }

    public function nilaiUraian(CtResponse $respons, User $penilai, float $skor, ?string $catatan): CtScore
    {
        $respons->forceFill([
            'skor_manual' => max(0, min((float) $respons->item->skor_maks, $skor)),
            'penilai_id' => $penilai->getKey(),
            'catatan_penilai' => $catatan,
        ])->save();

        return $this->hitungUlang($respons->user, $respons->item->test);
    }

    public function hitungUlang(User $siswa, CtTest $tes): CtScore
    {
        $respons = $siswa->ctResponses()->whereIn('ct_item_id', $tes->items()->pluck('id'))->get()->keyBy('ct_item_id');

        $baris = $tes->items->map(fn (CtItem $b) => [
            'indikator' => $b->indikator,
            'skor_maks' => $b->skor_maks,
            'skor' => $respons->get($b->getKey())?->skorBerlaku(),
        ])->all();

        $rekap = $this->penskor->rekap($baris);
        $skor = $this->mulai($siswa, $tes);
        $skor->forceFill([
            'skor_d' => $rekap['skor_d'], 'skor_p' => $rekap['skor_p'], 'skor_a' => $rekap['skor_a'], 'skor_al' => $rekap['skor_al'],
            'skor_total' => $rekap['skor_total'], 'persen' => $rekap['persen'],
            'dihitung_pada' => $rekap['lengkap'] && $skor->dikumpulkan_pada !== null ? now() : null,
        ])->save();

        return $skor;
    }
}
