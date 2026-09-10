<?php

namespace App\Services\Research;

use App\Models\AikenResult;
use App\Models\ValidationInstrument;
use App\Models\ValidationItem;
use Illuminate\Support\Collection;

/**
 * Lapisan penyimpanan V-02: mengambil skor dari validasi berstatus `selesai`,
 * menghitung Aiken's V per butir dengan AikenCalculator (murni), menyimpan ke
 * aiken_results, dan merangkum per aspek & keseluruhan. Produk valid bila
 * kategori minimal "sedang" (kriteria proposal).
 */
final class RekapAiken
{
    /**
     * @return array{
     *   butir: Collection<int, array{item: ValidationItem, v: float|null, kategori: string|null, n: int, saran: list<string>}>,
     *   aspek: array<string, array{nilai_v: float, kategori: string, n_butir: int}>,
     *   keseluruhan: array{nilai_v: float, kategori: string, n_butir: int},
     *   n_penilai: int, perlu_revisi: int, memenuhi: int
     * }
     */
    public function hitung(ValidationInstrument $instrumen): array
    {
        $kalkulator = new AikenCalculator(
            skalaMaks: $instrumen->skala_maks,
            skorTerendah: (int) config('geulis.aiken.skor_terendah', 1),
            kategori: config('geulis.aiken.kategori', [[0.80, 'tinggi'], [0.40, 'sedang'], [0.00, 'rendah']]),
        );

        $selesai = $instrumen->validations()->where('status', 'selesai')->pluck('id');
        $items = $instrumen->items()->with(['ratings' => fn ($q) => $q->whereIn('expert_validation_id', $selesai)])->get();

        $hasilButir = [];
        $butir = $items->map(function (ValidationItem $item) use ($kalkulator, &$hasilButir): array {
            $skor = $item->ratings->pluck('skor')->map(fn ($s) => (int) $s)->all();
            $saran = $item->ratings->pluck('saran')->filter(fn ($s) => filled($s))->values()->all();

            if ($skor === []) {
                return ['item' => $item, 'v' => null, 'kategori' => null, 'n' => 0, 'saran' => $saran];
            }

            $h = $kalkulator->hitungButir($skor);
            $hasilButir[$item->getKey()] = $h;

            AikenResult::query()->create([
                'validation_item_id' => $item->getKey(),
                'nilai_v' => $h['nilai_v'],
                'kategori' => $h['kategori'],
                'n_penilai' => $h['n_penilai'],
                'dihitung_pada' => now(),
            ]);

            return ['item' => $item, 'v' => $h['nilai_v'], 'kategori' => $h['kategori'], 'n' => $h['n_penilai'], 'saran' => $saran];
        });

        $aspek = [];
        foreach ($items->groupBy('aspek') as $nama => $grup) {
            $aspek[$nama] = $kalkulator->rerata(array_intersect_key($hasilButir, $grup->pluck('id')->flip()->all()));
        }

        $dinilai = $butir->filter(fn ($b) => $b['v'] !== null);

        return [
            'butir' => $butir,
            'aspek' => $aspek,
            'keseluruhan' => $kalkulator->rerata($hasilButir),
            'n_penilai' => $selesai->count(),
            'perlu_revisi' => $dinilai->filter(fn ($b) => $b['kategori'] === 'rendah')->count(),
            'memenuhi' => $dinilai->filter(fn ($b) => $b['kategori'] !== 'rendah')->count(),
        ];
    }
}
