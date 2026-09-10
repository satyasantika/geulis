<?php

namespace App\Livewire\Observasi;

use App\Models\Classroom;
use App\Models\Meeting;
use App\Models\Observation;
use App\Models\ObservationItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * O-01 Lembar observasi keterlaksanaan — diisi observer dari HP di belakang
 * kelas. Seluruh isian dipegang Alpine dan diantre di localStorage; dikirim
 * sekaligus lewat kirim() saat ada sinyal, otomatis diulang pada event
 * `online`. Satu lembar per (kelas, pertemuan, tanggal) per observer.
 */
#[Title('Lembar Observasi')]
class LembarObservasi extends Component
{
    /**
     * @param  array{classroom_id: int, meeting_id: int, tanggal: string, skor: array<int|string, int>, catatan?: string|null}  $isian
     * @return array{ok: bool, pesan: string}
     */
    public function kirim(array $isian): array
    {
        $kelas = Classroom::query()->find($isian['classroom_id'] ?? 0);
        $meeting = Meeting::query()->find($isian['meeting_id'] ?? 0);
        $tanggal = $isian['tanggal'] ?? null;
        $skor = $isian['skor'] ?? [];

        if ($kelas === null || $meeting === null || ! is_string($tanggal) || ! strtotime($tanggal)) {
            return ['ok' => false, 'pesan' => 'Kelas, pertemuan, dan tanggal wajib diisi.'];
        }

        $items = ObservationItem::query()->pluck('id');
        foreach ($items as $id) {
            $nilai = (int) ($skor[$id] ?? $skor[(string) $id] ?? 0);
            if ($nilai < 1 || $nilai > 4) {
                return ['ok' => false, 'pesan' => 'Semua butir harus diberi skor 1–4.'];
            }
        }

        DB::transaction(function () use ($kelas, $meeting, $tanggal, $skor, $items, $isian): void {
            $hari = date('Y-m-d', strtotime($tanggal));
            $observasi = Observation::query()
                ->where('observer_id', auth()->id())->where('classroom_id', $kelas->getKey())->where('meeting_id', $meeting->getKey())
                ->whereDate('tanggal', $hari)->first()
                ?? new Observation(['observer_id' => auth()->id(), 'classroom_id' => $kelas->getKey(), 'meeting_id' => $meeting->getKey(), 'tanggal' => $hari]);
            $observasi->catatan_lapangan = isset($isian['catatan']) ? mb_substr((string) $isian['catatan'], 0, 4000) : null;
            $observasi->save();
            foreach ($items as $id) {
                $observasi->records()->updateOrCreate(['observation_item_id' => $id], ['skor' => (int) ($skor[$id] ?? $skor[(string) $id])]);
            }
        });

        return ['ok' => true, 'pesan' => 'Terkirim.'];
    }

    public function render(): View
    {
        return view('livewire.observasi.lembar-observasi', [
            'kelas' => Classroom::query()->with('school')->where('aktif', true)->orderBy('nama')->get(),
            'pertemuan' => Meeting::query()->orderBy('urutan')->get(),
            'butir' => ObservationItem::query()->orderBy('urutan')->get(),
            'terkirim' => Observation::query()->with(['classroom', 'meeting'])->where('observer_id', auth()->id())->latest('tanggal')->limit(10)->get(),
            'kunciLokal' => 'geulis-observasi-'.auth()->id(),
        ]);
    }
}
