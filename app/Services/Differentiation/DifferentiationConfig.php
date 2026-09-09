<?php

namespace App\Services\Differentiation;

/**
 * Parameter Mesin Diferensiasi.
 *
 * PENTING: seluruh nilai di sini IKUT DIVALIDASI AHLI (lihat lembar validasi
 * butir "Nilai alpha pada pembaruan penguasaan sudah wajar"). Karena itu
 * nilainya tidak boleh ditanam diam-diam di dalam logika, melainkan
 * dikumpulkan di satu tempat, dapat disetel lewat config/.env, dan
 * dilaporkan apa adanya di dalam artikel.
 */
final class DifferentiationConfig
{
    public function __construct(
        /** Bobot riwayat pada rerata bergerak berbobot: M_baru = alpha*M_lama + (1-alpha)*s */
        public readonly float $alpha = 0.4,

        /** Ambang penempatan awal berdasarkan skor tes kesiapan R (0..100) */
        public readonly int $ambangL2 = 60,
        public readonly int $ambangL3 = 80,

        /** Ambang keputusan penguasaan M (0..1) */
        public readonly float $ambangPromosi = 0.80,
        public readonly float $ambangRemedial = 0.50,

        /** Batas pengulangan remedial sebelum dieskalasi ke guru */
        public readonly int $maksIterasiRemedial = 2,

        /** Selisih skor profil di bawah nilai ini dianggap "campuran" */
        public readonly int $selisihModusCampuran = 10,

        /** Deteksi menebak: durasi < rasio ini terhadap median kelas DAN skor rendah */
        public readonly float $rasioDurasiMenebak = 0.20,
        public readonly float $skorRendahMenebak = 0.40,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            alpha: (float) config('geulis.alpha', 0.4),
            ambangL2: (int) config('geulis.ambang_l2', 60),
            ambangL3: (int) config('geulis.ambang_l3', 80),
            ambangPromosi: (float) config('geulis.ambang_promosi', 0.80),
            ambangRemedial: (float) config('geulis.ambang_remedial', 0.50),
            maksIterasiRemedial: (int) config('geulis.maks_iterasi_remedial', 2),
        );
    }

    public function toArray(): array
    {
        return [
            'alpha' => $this->alpha,
            'ambang_l2' => $this->ambangL2,
            'ambang_l3' => $this->ambangL3,
            'ambang_promosi' => $this->ambangPromosi,
            'ambang_remedial' => $this->ambangRemedial,
            'maks_iterasi_remedial' => $this->maksIterasiRemedial,
        ];
    }
}
