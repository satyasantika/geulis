<div>
    <a href="{{ route('siswa.jalur') }}" wire:navigate class="text-sm text-tinta-3">&larr; Jalur belajar</a>
    <div class="mt-2 text-xs text-tinta-3">Pertemuan {{ $meeting->urutan }} · {{ ucfirst($meeting->materi) }} · fokus {{ str_replace('_', ' ', $meeting->fokus_ct) }}</div>
    <h1 class="text-xl font-bold">{{ $meeting->judul }}</h1>
    <p class="mt-2 text-sm text-tinta-2">{{ $meeting->capaian_pembelajaran }}</p>

    <ol class="mt-5 space-y-2">
        @foreach ($daftarUnit as $b)
            @php $u = $b['unit']; @endphp
            <li wire:key="unit-{{ $u->id }}">
                @if ($b['terbuka'])
                    <a href="{{ route('siswa.unit', $u) }}" wire:navigate class="flex items-center gap-3 rounded-xl border bg-white p-3 {{ $b['selesai'] ? 'border-garis-2' : 'border-aksen' }}">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $b['selesai'] ? 'bg-green-50 text-ok' : 'bg-aksen text-white' }}">{{ $b['selesai'] ? '✓' : $u->urutan }}</span>
                        <span class="flex-1">
                            <span class="block text-sm font-semibold">{{ $u->judul }}</span>
                            <span class="block text-xs text-tinta-3">{{ $b['selesai'] ? 'Selesai' : 'Buka' }}</span>
                        </span>
                    </a>
                @else
                    <div class="flex items-center gap-3 rounded-xl border border-dashed border-garis p-3 text-tinta-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-garis-2 text-sm font-bold">{{ $u->urutan }}</span>
                        <span class="text-sm">{{ $u->judul }}</span>
                    </div>
                @endif
            </li>
        @endforeach
    </ol>

    @if ($berikutnya)
        <a href="{{ route('siswa.unit', $berikutnya['unit']) }}" wire:navigate class="mt-5 block rounded-lg bg-aksen px-4 py-3 text-center font-semibold text-white">
            Lanjut: {{ $berikutnya['unit']->judul }}
        </a>
    @else
        <p class="mt-5 rounded-lg border border-ok/40 bg-green-50 px-3 py-2 text-sm text-ok">Pertemuan ini sudah selesai. Pertemuan berikutnya terbuka di jalur belajarmu.</p>
    @endif
</div>
