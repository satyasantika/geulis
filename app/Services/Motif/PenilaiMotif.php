<?php

namespace App\Services\Motif;

use App\Models\Activity;
use App\Models\MotifSubmission;
use App\Models\User;
use InvalidArgumentException;

/**
 * Lapisan penyimpanan Motif Builder: menjalankan urutan perintah siswa DAN
 * urutan acuan pada MesinTransformasi (server), merasterkan keduanya,
 * menilai dengan MotifScorer, lalu menyimpan ke motif_submissions.
 * Raster dari klien tidak pernah dipercaya — hanya cuplikan SVG-nya disimpan
 * sebagai ilustrasi.
 *
 * Konfigurasi aktivitas (activities.konfigurasi) untuk tipe motif_builder:
 *   kisi{min,maks}, motif_dasar[[x,y]…], sasaran[perintah…], langkah_minimum,
 *   blok[…], kunci{motif_dasar[], jumlah_motif_dasar, n_pengulangan, parameter{}}
 */
final class PenilaiMotif
{
    public const int MAKS_PERCOBAAN_SEBELUM_LANJUT = 3;

    public function __construct(
        private readonly MesinTransformasi $mesin = new MesinTransformasi,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $perintah
     * @param  list<int>  $ditandai
     */
    public function nilai(User $siswa, Activity $aktivitas, array $perintah, array $ditandai, ?string $svg = null): MotifSubmission
    {
        $konfigurasi = $aktivitas->konfigurasi;
        $this->mesin->validasi($perintah);

        $hasilSiswa = $this->mesin->jalankan($konfigurasi['motif_dasar'], $perintah);
        $hasilSasaran = $this->sasaran($aktivitas);

        $rasterizer = $this->rasterizer($konfigurasi);
        $skor = $this->scorer()->nilai(
            $rasterizer->raster($hasilSiswa),
            $rasterizer->raster($hasilSasaran),
            $this->mesin->hitungLangkah($perintah),
            (int) ($konfigurasi['langkah_minimum'] ?? $this->mesin->hitungLangkah($konfigurasi['sasaran'])),
        );

        $ke = MotifSubmission::query()->where('user_id', $siswa->getKey())->where('activity_id', $aktivitas->getKey())->count() + 1;

        return MotifSubmission::query()->create([
            'user_id' => $siswa->getKey(),
            'activity_id' => $aktivitas->getKey(),
            'percobaan_ke' => $ke,
            'urutan_perintah' => $perintah,
            'skor_kemiripan' => $skor['skor_kemiripan'],
            'langkah_siswa' => $skor['langkah_siswa'],
            'langkah_minimum' => $skor['langkah_minimum'],
            'efisiensi' => $skor['efisiensi'],
            'motif_dasar_ditandai' => array_values(array_map('intval', $ditandai)),
            'lolos' => $skor['lolos'],
            'cuplikan_svg' => $svg !== null ? mb_substr($svg, 0, 60000) : null,
        ]);
    }

    /**
     * Bukti empat indikator CT dari satu kiriman (dihitung ulang, tidak disimpan).
     *
     * @return array<string, array<string, mixed>>
     */
    public function buktiCT(MotifSubmission $kiriman): array
    {
        $kunci = $kiriman->activity->konfigurasi['kunci'] ?? [];

        return $this->scorer()->buktiCT(
            $this->mesin->ratakan($kiriman->urutan_perintah),
            $kiriman->motif_dasar_ditandai ?? [],
            $kunci,
        );
    }

    /**
     * Poligon-poligon motif sasaran (dijalankan dari urutan acuan).
     *
     * @return list<list<array{float, float}>>
     */
    public function sasaran(Activity $aktivitas): array
    {
        $k = $aktivitas->konfigurasi;
        if (! isset($k['motif_dasar'], $k['sasaran'])) {
            throw new InvalidArgumentException('Konfigurasi Motif Builder belum lengkap (motif_dasar, sasaran).');
        }

        return $this->mesin->jalankan($k['motif_dasar'], $k['sasaran']);
    }

    /** @param  array<string, mixed>  $konfigurasi */
    private function rasterizer(array $konfigurasi): Rasterizer
    {
        return new Rasterizer(
            (int) config('geulis.motif_resolusi', 200),
            (float) ($konfigurasi['kisi']['min'] ?? -6),
            (float) ($konfigurasi['kisi']['maks'] ?? 6),
        );
    }

    private function scorer(): MotifScorer
    {
        return new MotifScorer((int) config('geulis.motif_resolusi', 200), (float) config('geulis.motif_ambang_lolos', 90.0));
    }
}
