<div>
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
                <div class="mt-1 font-bold text-aksen">{{ $labelLevel[$siswa->enrollmentAktif()?->level_kini ?? $penempatan->level_awal] ?? $penempatan->level_awal }}</div>
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

        <h2 class="mt-6 font-semibold">Lima pertemuan</h2>
        <ol class="mt-3 space-y-3">
            @foreach ($jalur as $baris)
                @php $m = $baris['pertemuan']; @endphp
                <li wire:key="pertemuan-{{ $m->id }}">
                    @if ($baris['terbuka'])
                        <a href="{{ route('siswa.pertemuan', $m) }}" wire:navigate class="block rounded-xl border border-garis bg-white p-4 active:bg-aksen-latar">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-xs text-tinta-3">Pertemuan {{ $m->urutan }} · {{ ucfirst($m->materi) }}</div>
                                    <div class="font-semibold">{{ $m->judul }}</div>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-1 text-xs {{ $baris['selesai'] ? 'bg-green-50 text-ok' : 'bg-aksen-latar text-aksen' }}">
                                    {{ $baris['selesai'] ? 'Selesai' : $baris['unit_selesai'].'/'.$baris['unit_total'] }}
                                </span>
                            </div>
                        </a>
                    @else
                        <div class="rounded-xl border border-dashed border-garis p-4 text-tinta-3">
                            <div class="text-xs">Pertemuan {{ $m->urutan }} · {{ ucfirst($m->materi) }}</div>
                            <div class="font-semibold">{{ $m->judul }}</div>
                            <div class="mt-1 text-xs">{{ $m->terbit ? 'Terbuka setelah pertemuan sebelumnya selesai.' : 'Belum dibuka guru.' }}</div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</div>
