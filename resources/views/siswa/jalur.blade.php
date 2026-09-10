<x-layouts.app judul="Jalur Belajarmu">
    @php
        $siswa = auth()->user();
        $penempatan = $siswa->placement;
        $labelLevel = config('angket.label_level');
        $labelModus = config('angket.label_modus');
        $labelArtefak = config('angket.label_artefak');
    @endphp

    <h1 class="text-xl font-bold">Halo, {{ $siswa->nama }}</h1>

    @if ($penempatan === null)
        <div class="mt-4 rounded-xl border border-garis bg-white p-4">
            <p class="font-semibold">Mulai dari asesmen awal</p>
            <p class="mt-1 text-sm text-tinta-2">Tiga bagian singkat (±30 menit) supaya sistem tahu titik mulai dan cara belajar yang paling nyaman buatmu. Jawabanmu tersimpan otomatis.</p>
            <a href="{{ route('siswa.asesmen') }}" wire:navigate class="mt-4 block rounded-lg bg-aksen px-4 py-3 text-center font-semibold text-white">Mulai asesmen awal</a>
        </div>
    @else
        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-xl border border-garis bg-white p-3">
                <div class="text-xs text-tinta-3">Titik mulai</div>
                <div class="mt-1 font-bold text-aksen">{{ $labelLevel[$penempatan->level_awal] ?? $penempatan->level_awal }}</div>
            </div>
            <div class="rounded-xl border border-garis bg-white p-3">
                <div class="text-xs text-tinta-3">Cara belajar</div>
                <div class="mt-1 text-sm font-bold text-aksen">{{ $labelModus[$penempatan->modus] ?? $penempatan->modus }}</div>
            </div>
            <div class="rounded-xl border border-garis bg-white p-3">
                <div class="text-xs text-tinta-3">Artefak utama</div>
                <div class="mt-1 text-sm font-bold text-aksen">{{ $labelArtefak[$penempatan->artefak_utama] ?? $penempatan->artefak_utama }}</div>
            </div>
        </div>
        <p class="mt-3 text-xs text-tinta-3">Titik mulai bisa berubah seiring kemajuanmu — ini posisi sementara, bukan label.</p>

        <div class="mt-5 rounded-xl border border-dashed border-garis p-4 text-sm text-tinta-3">
            Lima pertemuan akan muncul di sini begitu gurumu membukanya.
        </div>
    @endif
</x-layouts.app>
