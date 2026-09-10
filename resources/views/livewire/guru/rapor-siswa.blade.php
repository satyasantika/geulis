<div>
    <a href="{{ $enrollment ? route('guru.papan', $enrollment->classroom) : route('guru.beranda') }}" wire:navigate class="text-sm text-tinta-3">&larr; Papan kelas</a>
    <h1 class="mt-2 text-xl font-bold">{{ $siswa->kode_anonim }} · {{ $siswa->nama }}</h1>
    <p class="text-sm text-tinta-2">NIS {{ $siswa->username }} · {{ $enrollment?->classroom->nama }} · persetujuan penelitian: {{ $siswa->consent === null ? 'belum dijawab' : ($siswa->consent->setuju_data_penelitian ? 'ya' : 'tidak (data dikeluarkan dari analisis)') }}</p>

    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-sm">
        <div class="rounded-xl border border-garis bg-white p-2"><div class="text-[10px] text-tinta-3">Level kini</div><div class="font-bold text-aksen">{{ $enrollment?->level_kini }} {{ $labelLevel[$enrollment?->level_kini] ?? '' }}</div></div>
        <div class="rounded-xl border border-garis bg-white p-2"><div class="text-[10px] text-tinta-3">Level awal · R</div><div class="font-bold">{{ $siswa->placement?->level_awal ?? '–' }} · {{ $siswa->placement?->skor_readiness ?? '–' }}</div></div>
        <div class="rounded-xl border border-garis bg-white p-2"><div class="text-[10px] text-tinta-3">Modus</div><div class="font-bold">{{ $labelModus[$siswa->placement?->modus] ?? '–' }}</div></div>
    </div>

    @if (! $formUbah)
        <button type="button" wire:click="$set('formUbah', true)" class="mt-3 w-full rounded-lg border border-aksen px-4 py-2 text-sm font-semibold text-aksen">Ubah level (override)</button>
    @else
        <form wire:submit="simpanUbah" class="mt-3 rounded-xl border border-aksen bg-aksen-latar p-3">
            <div class="grid grid-cols-3 gap-1">
                @foreach ($labelLevel as $kode => $nama)
                    <label class="rounded-lg border px-2 py-2 text-center text-sm has-[:checked]:border-aksen has-[:checked]:bg-white has-[:checked]:font-bold">
                        <input type="radio" class="sr-only" wire:model="levelBaru" value="{{ $kode }}">{{ $kode }}
                    </label>
                @endforeach
            </div>
            <textarea wire:model="alasan" rows="3" placeholder="Alasan (wajib)" class="mt-2 block w-full rounded-lg border border-garis px-3 py-2 text-base"></textarea>
            @error('alasan') <p class="mt-1 text-xs text-peringatan">{{ $message }}</p> @enderror
            <div class="mt-2 flex gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-aksen px-4 py-2 text-sm font-semibold text-white">Simpan</button>
                <button type="button" wire:click="$set('formUbah', false)" class="rounded-lg border border-garis px-4 py-2 text-sm text-tinta-2">Batal</button>
            </div>
        </form>
    @endif

    <section class="mt-5">
        <h2 class="text-sm font-semibold">Penguasaan per unit</h2>
        <ul class="mt-2 divide-y divide-garis-2 rounded-xl border border-garis bg-white text-sm">
            @forelse ($penguasaan as $p)
                <li class="flex items-center justify-between px-3 py-2" wire:key="m-{{ $p->id }}">
                    <span>P{{ $p->lessonUnit->meeting->urutan }} · {{ $p->lessonUnit->meeting->materi }}{{ $p->perlu_pendampingan ? ' · 🤝 perlu pendampingan' : '' }}</span>
                    <span class="font-mono {{ \App\Services\Guru\PapanKelasService::warna((float) $p->nilai_m) }} rounded px-2 py-0.5">{{ number_format($p->nilai_m, 2, ',') }} · {{ $p->level_saat_itu }}</span>
                </li>
            @empty
                <li class="px-3 py-3 text-tinta-3">Belum ada pemeriksaan penguasaan.</li>
            @endforelse
        </ul>
    </section>

    <section class="mt-5">
        <h2 class="text-sm font-semibold">Jejak keputusan sistem</h2>
        <ul class="mt-2 space-y-1 text-xs">
            @forelse ($jejak as $j)
                <li class="rounded-lg border border-garis bg-white px-3 py-2" wire:key="log-{{ $j->id }}">
                    <div class="flex justify-between text-tinta-3"><span>{{ $j->created_at->format('d/m H:i') }} · P{{ $j->lessonUnit->meeting->urutan }}</span><span class="font-mono">{{ $j->kode_aturan }}</span></div>
                    <div class="mt-0.5">{{ $j->keputusan }}</div>
                </li>
            @empty
                <li class="text-tinta-3">Belum ada.</li>
            @endforelse
        </ul>
    </section>

    @if ($overrides->isNotEmpty())
        <section class="mt-5">
            <h2 class="text-sm font-semibold">Override guru</h2>
            <ul class="mt-2 space-y-1 text-xs">
                @foreach ($overrides as $o)
                    <li class="rounded-lg border border-garis bg-white px-3 py-2" wire:key="ov-{{ $o->id }}"><b>{{ $o->level_lama }} → {{ $o->level_baru }}</b> · {{ $o->created_at->format('d/m H:i') }} · {{ $o->guru->nama }}<br>{{ $o->alasan }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="mt-5">
        <h2 class="text-sm font-semibold">Tes CT</h2>
        <ul class="mt-2 text-xs">
            @forelse ($skorCt as $s)
                <li class="rounded-lg border border-garis bg-white px-3 py-2" wire:key="ct-{{ $s->id }}">{{ $s->test->judul }}: <b>{{ number_format($s->persen, 1, ',') }}%</b> (D {{ $s->skor_d }} · P {{ $s->skor_p }} · A {{ $s->skor_a }} · Al {{ $s->skor_al }}){{ $s->dihitung_pada ? '' : ' · uraian belum lengkap' }}</li>
            @empty
                <li class="text-tinta-3">Belum mengerjakan tes.</li>
            @endforelse
        </ul>
    </section>

    <section class="mt-5">
        <h2 class="text-sm font-semibold">Motif Builder</h2>
        @if ($buktiCt)
            <p class="mt-1 text-xs text-tinta-2">Kiriman terakhir — dekomposisi: {{ $buktiCt['dekomposisi']['tepat'] }}/{{ $buktiCt['dekomposisi']['diharapkan'] }} tepat · pola: {{ $buktiCt['pengenalan_pola']['benar'] ? 'benar' : 'belum' }} · abstraksi: {{ round($buktiCt['abstraksi']['proporsi'] * 100) }}% parameter · algoritma: {{ $buktiCt['algoritma']['jumlah_langkah'] }} langkah{{ $buktiCt['algoritma']['memakai_perulangan'] ? ', memakai perulangan' : '' }}</p>
        @endif
        <div class="mt-2 grid grid-cols-3 gap-2">
            @forelse ($motif as $k)
                <div class="rounded-xl border border-garis bg-white p-1 text-center" wire:key="mt-{{ $k->id }}">
                    <div class="aspect-square overflow-hidden rounded-lg bg-latar">{!! $k->cuplikan_svg ?? '' !!}</div>
                    <div class="text-[10px] text-tinta-2">P{{ $k->activity->lessonUnit->meeting->urutan }} #{{ $k->percobaan_ke }} · {{ number_format($k->skor_kemiripan, 0) }}% · ef. {{ number_format($k->efisiensi, 0) }}%</div>
                </div>
            @empty
                <p class="col-span-3 text-xs text-tinta-3">Belum ada kiriman.</p>
            @endforelse
        </div>
    </section>
</div>
