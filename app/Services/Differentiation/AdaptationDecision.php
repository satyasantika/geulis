<?php

namespace App\Services\Differentiation;

/**
 * Hasil satu keputusan Mesin Diferensiasi.
 *
 * Objek ini sengaja membawa SEMUA yang dibutuhkan untuk tiga pembaca berbeda:
 *  - siswa   : $pesanSiswa  (bahasa manusia, tanpa menghakimi)
 *  - guru    : $kodeAturan + $keputusan
 *  - peneliti: seluruh isinya, disimpan utuh ke tabel adaptation_logs
 */
final class AdaptationDecision
{
    public const PROMOTE = 'RULE_PROMOTE';

    public const ENRICH = 'RULE_ENRICH';

    public const REINFORCE = 'RULE_REINFORCE';

    public const REMEDIATE = 'RULE_REMEDIATE';

    public const ESCALATE = 'RULE_ESCALATE';

    public const GUESS_GUARD = 'RULE_GUESS_GUARD';

    public function __construct(
        public readonly string $kodeAturan,
        public readonly float $mSebelum,
        public readonly float $mSesudah,
        public readonly float $skorPemeriksaan,
        public readonly string $levelSebelum,
        public readonly string $levelSesudah,
        public readonly string $keputusan,
        public readonly string $pesanSiswa,
        public readonly int $iterasiRemedial = 0,
        public readonly bool $perluPendampingan = false,
        public readonly array $konteks = [],
    ) {}

    public function toLogArray(): array
    {
        return [
            'kode_aturan' => $this->kodeAturan,
            'm_sebelum' => round($this->mSebelum, 3),
            'm_sesudah' => round($this->mSesudah, 3),
            'skor_pemeriksaan' => round($this->skorPemeriksaan, 3),
            'level_sebelum' => $this->levelSebelum,
            'level_sesudah' => $this->levelSesudah,
            'keputusan' => $this->keputusan,
            'konteks' => $this->konteks,
        ];
    }
}
