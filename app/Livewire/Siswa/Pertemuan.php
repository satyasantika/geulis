<?php

namespace App\Livewire\Siswa;

use App\Models\LessonUnit;
use App\Models\Meeting;
use App\Services\Konten\KemajuanSiswa;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** S-05 Kartu pertemuan: tujuh bagian tetap beserta statusnya. */
class Pertemuan extends Component
{
    #[Locked]
    public int $meetingId;

    public function mount(Meeting $meeting, KemajuanSiswa $kemajuan): void
    {
        if (! $kemajuan->pertemuanTerbuka(auth()->user(), $meeting)) {
            session()->flash('pesan', 'Pertemuan ini belum terbuka. Selesaikan pertemuan sebelumnya dulu, ya.');
            $this->redirectRoute('siswa.jalur', navigate: true);

            return;
        }

        $this->meetingId = $meeting->getKey();
    }

    public function render(KemajuanSiswa $kemajuan): View
    {
        $siswa = auth()->user();
        $meeting = Meeting::query()->with('lessonUnits')->findOrFail($this->meetingId);
        $selesai = $siswa->unitProgress()->where('status', 'selesai')->pluck('lesson_unit_id')->flip();

        $unit = $meeting->lessonUnits->map(fn (LessonUnit $u) => [
            'unit' => $u,
            'selesai' => $selesai->has($u->getKey()),
            'terbuka' => $kemajuan->unitTerbuka($siswa, $u),
        ]);

        return view('livewire.siswa.pertemuan', [
            'meeting' => $meeting,
            'daftarUnit' => $unit,
            'berikutnya' => $unit->first(fn (array $b) => ! $b['selesai'] && $b['terbuka']),
        ])->title("Pertemuan {$meeting->urutan}");
    }
}
