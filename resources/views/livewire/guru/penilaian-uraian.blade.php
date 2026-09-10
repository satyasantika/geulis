<div>
    <a href="{{ route('guru.beranda') }}" wire:navigate class="text-sm text-tinta-3">&larr; Beranda</a>
    <h1 class="mt-2 text-xl font-bold">Penilaian uraian tes CT</h1>
    <p class="text-sm text-tinta-2">{{ $antrean->count() }} jawaban menunggu. Skor 0–4 mengikuti rubrik butir.</p>

    <div class="mt-4 space-y-4">
        @forelse ($antrean as $r)
            <div class="rounded-xl border border-garis bg-white p-3" wire:key="r-{{ $r->id }}">
                <div class="text-xs text-tinta-3">{{ $r->item->test->judul }} · Soal {{ $r->item->urutan }} · {{ \App\Models\CtItem::INDIKATOR[$r->item->indikator] ?? $r->item->indikator }}</div>
                <div class="mt-1 text-sm font-semibold">{{ $r->user->kode_anonim ?? 'S-?' }} · {{ $r->user->nama }}</div>
                <p class="mt-2 text-sm text-tinta-2">{{ $r->item->pertanyaan }}</p>
                <blockquote class="mt-2 whitespace-pre-line rounded-lg bg-latar p-3 text-sm">{{ $r->jawaban ?? '(kosong)' }}</blockquote>

                <details class="mt-2 text-xs text-tinta-2">
                    <summary class="cursor-pointer font-semibold">Rubrik butir</summary>
                    <ul class="mt-1 space-y-0.5">
                        @foreach ($r->item->rubrik_butir ?? [] as $tingkat => $deskripsi)
                            <li><b>{{ $tingkat }}</b> — {{ $deskripsi }}</li>
                        @endforeach
                    </ul>
                </details>

                <div class="mt-3 grid grid-cols-5 gap-1">
                    @foreach (range(0, (int) $r->item->skor_maks) as $t)
                        <label class="rounded-lg border px-1 py-2 text-center text-sm has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar has-[:checked]:font-bold">
                            <input type="radio" class="sr-only" wire:model="skor.{{ $r->id }}" value="{{ $t }}">{{ $t }}
                        </label>
                    @endforeach
                </div>
                @error("skor.{$r->id}") <p class="mt-1 text-xs text-peringatan">{{ $message }}</p> @enderror
                <input type="text" wire:model="catatan.{{ $r->id }}" placeholder="Catatan untuk siswa (opsional)" class="mt-2 block w-full rounded-lg border border-garis px-3 py-2 text-sm">
                <button type="button" wire:click="nilai({{ $r->id }})" class="mt-2 block w-full rounded-lg bg-aksen px-4 py-2 text-sm font-semibold text-white">Simpan nilai</button>
            </div>
        @empty
            <p class="rounded-xl border border-dashed border-garis p-4 text-sm text-tinta-3">Tidak ada uraian yang menunggu.</p>
        @endforelse
    </div>
</div>
