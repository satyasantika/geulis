<div>
    <a href="{{ route('guru.beranda') }}" wire:navigate class="text-sm text-tinta-3">&larr; Beranda</a>
    <h1 class="mt-2 text-xl font-bold">Perlu pendampingan langsung</h1>
    <p class="text-sm text-tinta-2">{{ $daftar->count() }} siswa ditandai sistem setelah dua kali remedial tanpa perbaikan.</p>

    @foreach ($polaUnit as $grup)
        <p class="mt-3 rounded-lg border border-peringatan/40 bg-aksen-latar px-3 py-2 text-sm text-peringatan">
            Pola yang terlihat: {{ $grup->count() }} siswa tersendat di unit yang sama — {{ $grup->first()->lessonUnit->meeting->judul }} · {{ $grup->first()->lessonUnit->judul }}. Pertimbangkan membahasnya klasikal.
        </p>
    @endforeach

    <ul class="mt-4 divide-y divide-garis-2 rounded-xl border border-garis bg-white">
        @forelse ($daftar as $s)
            <li class="flex items-center justify-between gap-2 p-3" wire:key="pd-{{ $s->id }}">
                <div class="min-w-0">
                    <a href="{{ route('guru.siswa', $s->user) }}" wire:navigate class="block truncate text-sm font-semibold">{{ $s->user->kode_anonim ?? '' }} · {{ $s->user->nama }}</a>
                    <div class="text-xs text-tinta-3">P{{ $s->lessonUnit->meeting->urutan }} {{ $s->lessonUnit->judul }} · {{ $s->iterasi_remedial }}× remedial · M {{ number_format($s->nilai_m, 2, ',') }}</div>
                </div>
                <button type="button" wire:click="tandaiDitangani({{ $s->id }})" wire:confirm="Tandai sudah didampingi?" class="shrink-0 rounded-lg border border-garis px-2 py-1 text-xs text-tinta-2">Sudah didampingi</button>
            </li>
        @empty
            <li class="p-4 text-sm text-tinta-3">Tidak ada. Semua siswa berjalan tanpa eskalasi.</li>
        @endforelse
    </ul>
</div>
