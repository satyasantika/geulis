<?php

namespace App\Services\Differentiation;

/**
 * MESIN DIFERENSIASI — inti sistem GEULIS.
 *
 * Kelas ini sengaja ditulis sebagai logika murni tanpa sentuhan basis data:
 *  - mudah diuji unit (setiap aturan wajib punya kasus uji),
 *  - mudah dijelaskan ke validator ahli,
 *  - mudah dilaporkan apa adanya di dalam artikel.
 *
 * Penyimpanan hasil ke mastery_states / adaptation_logs dikerjakan oleh
 * App\Services\Differentiation\AdaptationRecorder (lapisan terpisah).
 *
 * Tiga dimensi diferensiasi (Tomlinson):
 *   KONTEN  -> level L1/L2/L3, ditentukan sistem dari bukti kinerja
 *   PROSES  -> modus visual/simbolik/naratif, dari profil belajar
 *   PRODUK  -> dipilih siswa (tidak diputuskan kelas ini)
 */
final class DifferentiationEngine
{
    private const URUTAN_LEVEL = ['L1', 'L2', 'L3'];

    public function __construct(
        private readonly DifferentiationConfig $config = new DifferentiationConfig,
    ) {}

    // ---------------------------------------------------------------
    // 1. PENEMPATAN AWAL
    // ---------------------------------------------------------------

    /**
     * @param  int  $skorReadiness  R, 0..100
     * @param  array  $profil  ['visual'=>int,'simbolik'=>int,'naratif'=>int]
     * @param  string  $artefakPilihan  batik|payung_geulis|anyaman
     */
    public function tempatkan(int $skorReadiness, array $profil, string $artefakPilihan): array
    {
        $level = match (true) {
            $skorReadiness < $this->config->ambangL2 => 'L1',
            $skorReadiness < $this->config->ambangL3 => 'L2',
            default => 'L3',
        };

        $modus = $this->tentukanModus($profil);

        return [
            'level_awal' => $level,
            'modus' => $modus,
            'artefak_utama' => $artefakPilihan,
            'penjelasan' => [
                'skor_readiness' => $skorReadiness,
                'aturan_level' => sprintf(
                    'R = %d; ambang L2 = %d, L3 = %d',
                    $skorReadiness, $this->config->ambangL2, $this->config->ambangL3
                ),
                'profil' => $profil,
                'aturan_modus' => 'modus utama = skor tertinggi; campuran bila selisih dua tertinggi < '
                                    .$this->config->selisihModusCampuran,
                'parameter' => $this->config->toArray(),
            ],
        ];
    }

    private function tentukanModus(array $profil): string
    {
        $skor = [
            'visual' => (int) ($profil['visual'] ?? 0),
            'simbolik' => (int) ($profil['simbolik'] ?? 0),
            'naratif' => (int) ($profil['naratif'] ?? 0),
        ];
        arsort($skor);
        $nilai = array_values($skor);

        if (($nilai[0] - $nilai[1]) < $this->config->selisihModusCampuran) {
            return 'campuran';
        }

        return (string) array_key_first($skor);
    }

    // ---------------------------------------------------------------
    // 2. ADAPTASI BERKELANJUTAN
    // ---------------------------------------------------------------

    /**
     * Dijalankan setiap kali siswa menyelesaikan satu Pemeriksaan Penguasaan.
     *
     * @param  float  $mLama  penguasaan sebelumnya, 0..1
     * @param  float  $skorPemeriksaan  s = proporsi skor pemeriksaan terakhir, 0..1
     * @param  string  $levelKini  L1|L2|L3
     * @param  int  $iterasiRemedial  berapa kali unit ini sudah diremedial
     * @param  array  $sinyal  ['durasi_detik'=>int, 'median_durasi_kelas'=>int, 'butir_salah'=>array]
     */
    public function evaluasi(
        float $mLama,
        float $skorPemeriksaan,
        string $levelKini,
        int $iterasiRemedial = 0,
        array $sinyal = [],
    ): AdaptationDecision {
        $this->pastikanLevelSah($levelKini);
        $s = max(0.0, min(1.0, $skorPemeriksaan));

        // --- Penjaga tebak-tebakan: dijalankan LEBIH DAHULU, tidak mengubah M.
        if ($this->tampakMenebak($s, $sinyal)) {
            return new AdaptationDecision(
                kodeAturan: AdaptationDecision::GUESS_GUARD,
                mSebelum: $mLama,
                mSesudah: $mLama,                       // sengaja tidak diubah
                skorPemeriksaan: $s,
                levelSebelum: $levelKini,
                levelSesudah: $levelKini,
                keputusan: 'Pengerjaan terlalu cepat dengan skor rendah; diminta mengulang tanpa penurunan level.',
                pesanSiswa: 'Sepertinya bagian ini dikerjakan terlalu cepat. Coba sekali lagi dengan lebih tenang, ya — hasilnya belum dihitung.',
                iterasiRemedial: $iterasiRemedial,
                konteks: ['sinyal' => $sinyal, 'parameter' => $this->config->toArray()],
            );
        }

        // --- Pembaruan penguasaan: rerata bergerak berbobot.
        $mBaru = $this->config->alpha * $mLama + (1 - $this->config->alpha) * $s;
        $mBaru = round(max(0.0, min(1.0, $mBaru)), 3);

        $konteks = [
            'butir_salah' => $sinyal['butir_salah'] ?? [],
            'parameter' => $this->config->toArray(),
            'rumus' => 'M_baru = alpha*M_lama + (1-alpha)*s',
        ];

        // --- RULE_PROMOTE / RULE_ENRICH
        if ($mBaru >= $this->config->ambangPromosi) {
            if ($levelKini !== 'L3') {
                $levelBaru = $this->naikSatuTingkat($levelKini);

                return new AdaptationDecision(
                    kodeAturan: AdaptationDecision::PROMOTE,
                    mSebelum: $mLama, mSesudah: $mBaru, skorPemeriksaan: $s,
                    levelSebelum: $levelKini, levelSesudah: $levelBaru,
                    keputusan: "Penguasaan {$mBaru} >= {$this->config->ambangPromosi}; level naik {$levelKini} -> {$levelBaru}, latihan rutin dilewati, tugas pengayaan dibuka.",
                    pesanSiswa: 'Bagus! Penguasaanmu sudah kuat, jadi kamu langsung lanjut ke tantangan yang lebih dalam.',
                    iterasiRemedial: 0,
                    konteks: $konteks,
                );
            }

            return new AdaptationDecision(
                kodeAturan: AdaptationDecision::ENRICH,
                mSebelum: $mLama, mSesudah: $mBaru, skorPemeriksaan: $s,
                levelSebelum: 'L3', levelSesudah: 'L3',
                keputusan: 'Sudah di level tertinggi; tantangan Motif Builder tingkat lanjut dibuka.',
                pesanSiswa: 'Kamu sudah menguasai bagian ini. Ada tantangan Motif Builder tingkat lanjut yang menunggu.',
                iterasiRemedial: 0,
                konteks: $konteks,
            );
        }

        // --- RULE_REINFORCE
        if ($mBaru >= $this->config->ambangRemedial) {
            return new AdaptationDecision(
                kodeAturan: AdaptationDecision::REINFORCE,
                mSebelum: $mLama, mSesudah: $mBaru, skorPemeriksaan: $s,
                levelSebelum: $levelKini, levelSesudah: $levelKini,
                keputusan: "Penguasaan {$mBaru} berada di rentang penguatan; tetap {$levelKini} + latihan terpilih dari butir yang salah.",
                pesanSiswa: 'Sudah lumayan. Ada beberapa bagian yang masih perlu dimantapkan — latihannya sudah disiapkan.',
                iterasiRemedial: $iterasiRemedial,
                konteks: $konteks,
            );
        }

        // --- RULE_ESCALATE (batas remedial terlampaui)
        if ($iterasiRemedial >= $this->config->maksIterasiRemedial) {
            return new AdaptationDecision(
                kodeAturan: AdaptationDecision::ESCALATE,
                mSebelum: $mLama, mSesudah: $mBaru, skorPemeriksaan: $s,
                levelSebelum: $levelKini, levelSesudah: $levelKini,
                keputusan: "Remedial sudah {$iterasiRemedial}x tanpa perbaikan; ditandai untuk pendampingan guru, materi berikutnya tetap dibuka.",
                pesanSiswa: 'Kamu sudah beberapa kali mencoba bagian ini. Gurumu sudah diberi tahu supaya bisa membantu langsung. Materi berikutnya tetap terbuka.',
                iterasiRemedial: $iterasiRemedial,
                perluPendampingan: true,
                konteks: $konteks,
            );
        }

        // --- RULE_REMEDIATE
        $levelBaru = $this->turunSatuTingkat($levelKini);

        return new AdaptationDecision(
            kodeAturan: AdaptationDecision::REMEDIATE,
            mSebelum: $mLama, mSesudah: $mBaru, skorPemeriksaan: $s,
            levelSebelum: $levelKini, levelSesudah: $levelBaru,
            keputusan: "Penguasaan {$mBaru} < {$this->config->ambangRemedial}; turun {$levelKini} -> {$levelBaru} dengan perancah, lalu diperiksa ulang (iterasi ".($iterasiRemedial + 1).').',
            pesanSiswa: 'Kita pelan-pelan dulu, ya. Bagian ini akan ditampilkan dengan versi yang lebih terbimbing sebelum kamu mencoba lagi.',
            iterasiRemedial: $iterasiRemedial + 1,
            konteks: $konteks,
        );
    }

    // ---------------------------------------------------------------
    // 3. PEMILIHAN VARIAN KONTEN
    // ---------------------------------------------------------------

    /**
     * Memilih satu varian konten dari daftar yang tersedia untuk satu unit.
     * Kecocokan lebih spesifik menang; '*' berarti berlaku untuk semua.
     *
     * @param  array  $varian  daftar ['id'=>int,'level'=>string,'modus'=>string]
     */
    public function pilihVarian(array $varian, string $level, string $modus): ?array
    {
        $terbaik = null;
        $skorTerbaik = -1;

        foreach ($varian as $v) {
            $vLevel = $v['level'] ?? '*';
            $vModus = $v['modus'] ?? '*';

            if ($vLevel !== '*' && $vLevel !== $level) {
                continue;
            }
            // modus 'campuran' cocok dengan varian mana pun; sistem menyajikan dua modus berdampingan
            if ($vModus !== '*' && $modus !== 'campuran' && ! str_contains($vModus, $modus)) {
                continue;
            }

            $skor = ($vLevel !== '*' ? 2 : 0) + ($vModus !== '*' ? 1 : 0);
            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $terbaik = $v;
            }
        }

        return $terbaik;
    }

    // ---------------------------------------------------------------
    // Pembantu
    // ---------------------------------------------------------------

    private function tampakMenebak(float $s, array $sinyal): bool
    {
        $durasi = $sinyal['durasi_detik'] ?? null;
        $median = $sinyal['median_durasi_kelas'] ?? null;

        if ($durasi === null || $median === null || $median <= 0) {
            return false;
        }

        return ($durasi / $median) < $this->config->rasioDurasiMenebak
            && $s < $this->config->skorRendahMenebak;
    }

    private function naikSatuTingkat(string $level): string
    {
        $i = array_search($level, self::URUTAN_LEVEL, true);

        return self::URUTAN_LEVEL[min($i + 1, count(self::URUTAN_LEVEL) - 1)];
    }

    private function turunSatuTingkat(string $level): string
    {
        $i = array_search($level, self::URUTAN_LEVEL, true);

        return self::URUTAN_LEVEL[max($i - 1, 0)];
    }

    private function pastikanLevelSah(string $level): void
    {
        if (! in_array($level, self::URUTAN_LEVEL, true)) {
            throw new \InvalidArgumentException("Level tidak dikenal: {$level}");
        }
    }
}
