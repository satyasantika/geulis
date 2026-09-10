<?php

namespace App\Livewire\Siswa;

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Models\LessonUnit;
use App\Models\Reflection;
use App\Models\User;
use App\Services\Differentiation\AdaptationDecision;
use App\Services\Differentiation\AdaptationRecorder;
use App\Services\Konten\KemajuanSiswa;
use App\Services\Konten\PenskorKuis;
use App\Services\Konten\VariantResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * S-05/S-06/S-08 Satu unit (bagian) pertemuan: varian konten sesuai level &
 * modus, GeoGebra swadaya, aktivitas (kuis/refleksi), dan umpan balik
 * adaptif setelah pemeriksaan penguasaan.
 */
class Unit extends Component
{
    #[Locked]
    public int $unitId;

    #[Locked]
    public ?int $attemptId = null;

    /** @var array<int, int|null> */
    public array $jawaban = [];

    /** @var array<int, string> */
    public array $refleksi = [];

    /** @var array<string, mixed>|null Hasil kuis terakhir (S-08). */
    public ?array $hasil = null;

    public function mount(LessonUnit $lessonUnit, KemajuanSiswa $kemajuan): void
    {
        if (! $kemajuan->unitTerbuka($this->siswa(), $lessonUnit)) {
            session()->flash('pesan', 'Bagian ini belum terbuka. Selesaikan bagian sebelumnya dulu, ya.');
            $this->redirectRoute('siswa.pertemuan', $lessonUnit->meeting, navigate: true);

            return;
        }

        $this->unitId = $lessonUnit->getKey();
        $kemajuan->tandaiSedang($this->siswa(), $lessonUnit);
        $this->mulaiPercobaan();
    }

    // ---------- Kuis ----------

    public function kirimKuis(PenskorKuis $penskor, AdaptationRecorder $recorder, KemajuanSiswa $kemajuan): void
    {
        $aktivitas = $this->aktivitasKuis();
        if ($aktivitas === null) {
            return;
        }

        $butir = $aktivitas->konfigurasi['butir'] ?? [];
        foreach (array_keys($butir) as $i) {
            if (! isset($this->jawaban[$i])) {
                $this->addError('jawaban', 'Jawab semua butir dulu, ya.');

                return;
            }
        }

        $skor = $penskor->skor($butir, $this->jawaban);
        $attempt = ActivityAttempt::query()->findOrFail($this->attemptId);
        $durasi = max(1, now()->diffInSeconds($attempt->mulai_pada, true));

        $attempt->fill([
            'jawaban' => $this->jawaban,
            'skor' => $skor['proporsi'] * 100,
            'durasi_detik' => $durasi,
            'selesai_pada' => now(),
        ])->save();

        $this->hasil = ['benar' => $skor['benar'], 'total' => $skor['total'], 'keputusan' => null];
        $unit = $this->unit();

        if (! $unit->memicu_adaptasi) {
            $kemajuan->tandaiSelesai($this->siswa(), $unit);
            $this->jawaban = [];

            return;
        }

        $median = $penskor->median(
            ActivityAttempt::query()->where('activity_id', $aktivitas->getKey())
                ->whereNotNull('selesai_pada')->where('user_id', '!=', $this->siswa()->getKey())
                ->pluck('durasi_detik')->all()
        );

        $keputusan = $recorder->evaluasiDanRekam($this->siswa(), $unit, $skor['proporsi'], [
            'durasi_detik' => $durasi,
            'median_durasi_kelas' => $median,
            'butir_salah' => $skor['butir_salah'],
        ]);

        if ($keputusan->kodeAturan === AdaptationDecision::GUESS_GUARD) {
            $attempt->forceFill(['dugaan_menebak' => true])->save();
        }

        $this->hasil['keputusan'] = [
            'kode' => $keputusan->kodeAturan,
            'pesan' => $keputusan->pesanSiswa,
            'm_sebelum' => $keputusan->mSebelum,
            'm_sesudah' => $keputusan->mSesudah,
            'level_sebelum' => $keputusan->levelSebelum,
            'level_sesudah' => $keputusan->levelSesudah,
            'iterasi' => $keputusan->iterasiRemedial,
            'butir_salah' => $skor['butir_salah'],
            'ulang' => in_array($keputusan->kodeAturan, [AdaptationDecision::REMEDIATE, AdaptationDecision::GUESS_GUARD], true),
        ];

        if (! $this->hasil['keputusan']['ulang']) {
            $kemajuan->tandaiSelesai($this->siswa(), $unit);
        }

        $this->jawaban = [];
    }

    /** Coba lagi setelah remedial/penjaga tebakan: percobaan baru, varian sesuai level baru. */
    public function cobaLagi(): void
    {
        $this->hasil = null;
        $this->attemptId = null;
        $this->mulaiPercobaan();
    }

    // ---------- Refleksi & unit tanpa aktivitas ----------

    public function kirimRefleksi(KemajuanSiswa $kemajuan): void
    {
        $pertanyaan = $this->pertanyaanRefleksi();
        foreach ($pertanyaan as $i => $p) {
            if (trim($this->refleksi[$i] ?? '') === '') {
                $this->addError('refleksi', 'Isi kedua pertanyaan dulu, ya — tidak ada jawaban salah.');

                return;
            }
        }

        foreach ($pertanyaan as $i => $p) {
            Reflection::query()->updateOrCreate(
                ['user_id' => $this->siswa()->getKey(), 'meeting_id' => $this->unit()->meeting_id, 'pertanyaan' => $p],
                ['jawaban' => trim($this->refleksi[$i])],
            );
        }

        $kemajuan->tandaiSelesai($this->siswa(), $this->unit());
        $this->redirectRoute('siswa.pertemuan', $this->unit()->meeting, navigate: true);
    }

    public function selesai(KemajuanSiswa $kemajuan): void
    {
        $kemajuan->tandaiSelesai($this->siswa(), $this->unit());
        $this->redirectRoute('siswa.pertemuan', $this->unit()->meeting, navigate: true);
    }

    // ---------- Pembantu ----------

    protected function mulaiPercobaan(): void
    {
        $aktivitas = $this->aktivitasKuis();
        if ($aktivitas === null) {
            return;
        }

        $terbuka = ActivityAttempt::query()
            ->where('user_id', $this->siswa()->getKey())->where('activity_id', $aktivitas->getKey())
            ->whereNull('selesai_pada')->latest('id')->first();

        if ($terbuka === null) {
            $ke = ActivityAttempt::query()->where('user_id', $this->siswa()->getKey())->where('activity_id', $aktivitas->getKey())->count() + 1;
            $terbuka = ActivityAttempt::query()->create([
                'user_id' => $this->siswa()->getKey(),
                'activity_id' => $aktivitas->getKey(),
                'percobaan_ke' => $ke,
                'mulai_pada' => now(),
            ]);
        }

        $this->attemptId = $terbuka->getKey();
    }

    public function unit(): LessonUnit
    {
        return LessonUnit::query()->with('meeting')->findOrFail($this->unitId);
    }

    /** @return Collection<int, Activity> */
    public function aktivitas(): Collection
    {
        $level = $this->siswa()->enrollmentAktif()?->level_kini ?? '*';

        return $this->unit()->activities()->get()
            ->filter(fn (Activity $a) => $a->level === '*' || $a->level === $level)
            ->values();
    }

    public function aktivitasKuis(): ?Activity
    {
        return $this->aktivitas()->first(fn (Activity $a) => $a->tipe === 'kuis');
    }

    /** @return list<string> */
    public function pertanyaanRefleksi(): array
    {
        $a = $this->aktivitas()->first(fn (Activity $a) => $a->tipe === 'refleksi');

        return $a?->konfigurasi['pertanyaan'] ?? [
            'Bagian mana yang paling membuatmu paham hari ini, dan mengapa?',
            'Kalau kamu menjelaskan materi ini ke teman, apa yang akan kamu katakan pertama kali?',
        ];
    }

    protected function siswa(): User
    {
        return auth()->user();
    }

    public function render(VariantResolver $resolver, KemajuanSiswa $kemajuan): View
    {
        $unit = $this->unit();
        $siswa = $this->siswa();
        $varian = $resolver->untuk($siswa, $unit);
        $meeting = $unit->meeting;
        $berikut = $meeting->lessonUnits()->where('urutan', '>', $unit->urutan)->orderBy('urutan')->first();

        return view('livewire.siswa.unit', [
            'unit' => $unit,
            'meeting' => $meeting,
            'varian' => $varian,
            'aset' => $varian?->culturalAsset,
            'kuis' => $this->aktivitasKuis(),
            'motif' => $this->aktivitas()->first(fn (Activity $a) => $a->tipe === 'motif_builder'),
            'pertanyaanRefleksi' => $this->pertanyaanRefleksi(),
            'selesai' => $kemajuan->unitSelesai($siswa, $unit),
            'berikut' => $berikut,
            'level' => $siswa->enrollmentAktif()?->level_kini ?? '?',
            'modus' => $siswa->enrollmentAktif()?->modus_kini ?? '?',
            'labelLevel' => config('angket.label_level'),
        ])->title($unit->judul);
    }
}
