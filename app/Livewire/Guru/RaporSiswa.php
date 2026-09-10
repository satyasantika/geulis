<?php

namespace App\Livewire\Guru;

use App\Models\User;
use App\Services\Guru\PengesampinganGuru;
use App\Services\Motif\PenilaiMotif;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** G-02 Rapor siswa perorangan + override. */
class RaporSiswa extends Component
{
    #[Locked]
    public int $siswaId;

    public string $levelBaru = '';

    public string $alasan = '';

    public bool $formUbah = false;

    public function mount(User $user): void
    {
        $enrollment = $user->enrollmentAktif();
        abort_unless($enrollment !== null && $enrollment->classroom->guru_id === auth()->id(), 403);

        $this->siswaId = $user->getKey();
        $this->levelBaru = $enrollment->level_kini;
    }

    public function simpanUbah(PengesampinganGuru $pengesampingan): void
    {
        $this->resetErrorBag('alasan');
        try {
            $pengesampingan->jalankan(auth()->user(), $this->siswa(), $this->levelBaru, $this->alasan);
        } catch (InvalidArgumentException $e) {
            $this->addError('alasan', $e->getMessage());

            return;
        }

        $this->formUbah = false;
        $this->alasan = '';
    }

    public function siswa(): User
    {
        return User::query()->with(['placement', 'consent'])->findOrFail($this->siswaId);
    }

    public function render(PenilaiMotif $penilai): View
    {
        $siswa = $this->siswa();
        $motif = $siswa->motifSubmissions()->with('activity.lessonUnit.meeting')->orderByDesc('id')->get();

        return view('livewire.guru.rapor-siswa', [
            'siswa' => $siswa,
            'enrollment' => $siswa->enrollmentAktif(),
            'penguasaan' => $siswa->masteryStates()->with('lessonUnit.meeting')->get()->sortBy(fn ($s) => $s->lessonUnit->meeting->urutan * 10 + $s->lessonUnit->urutan),
            'jejak' => $siswa->adaptationLogs()->with('lessonUnit.meeting')->orderByDesc('id')->limit(30)->get(),
            'overrides' => $siswa->overridesDiterima()->with('guru')->orderByDesc('id')->get(),
            'motif' => $motif,
            'buktiCt' => $motif->first() ? $penilai->buktiCT($motif->first()) : null,
            'skorCt' => $siswa->ctScores()->with('test')->get(),
            'labelLevel' => config('angket.label_level'),
            'labelModus' => config('angket.label_modus'),
        ])->title('Rapor '.$siswa->nama);
    }
}
