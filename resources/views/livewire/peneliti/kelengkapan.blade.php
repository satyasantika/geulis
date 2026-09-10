<div>
    <x-peneliti-nav aktif="kelengkapan" />
    <h1 class="mt-3 text-xl font-bold">Kelengkapan data</h1>
    <p class="text-sm text-tinta-2">{{ $totalSekolah }} sekolah · {{ $totalKelas }} kelas riset · {{ $totalSiswa }} siswa</p>

    <div class="mt-3 overflow-x-auto rounded-xl border border-garis bg-white">
        <table class="w-full text-xs">
            <thead class="bg-latar text-left"><tr>
                <th class="px-2 py-2">Kelas</th><th class="px-2 py-2">Kelompok</th><th class="px-2 py-2">n</th><th class="px-2 py-2">Asesmen awal</th><th class="px-2 py-2">Pretest</th>
                @foreach (range(1, 5) as $p) <th class="px-1 py-2 text-center">P{{ $p }}</th> @endforeach
                <th class="px-2 py-2">Posttest</th><th class="px-2 py-2">Angket</th>
            </tr></thead>
            <tbody>
                @forelse ($baris as $b)
                    <tr class="border-t border-garis-2" wire:key="k-{{ $b['kelas']->id }}">
                        <td class="whitespace-nowrap px-2 py-1.5"><b>{{ $b['kelas']->school?->nama }}</b> · {{ $b['kelas']->nama }}</td>
                        <td class="px-2 py-1.5 capitalize">{{ str_replace('_', ' ', $b['kelas']->kelompok_riset) }}</td>
                        <td class="px-2 py-1.5">{{ $b['n'] }}</td>
                        <td class="px-2 py-1.5 font-mono {{ $b['asesmen'] < $b['n'] ? 'text-peringatan' : 'text-ok' }}">{{ $b['asesmen'] }}/{{ $b['n'] }}</td>
                        <td class="px-2 py-1.5 font-mono {{ $b['pretest'] < $b['n'] ? 'text-peringatan' : 'text-ok' }}">{{ $b['pretest'] }}/{{ $b['n'] }}</td>
                        @foreach (range(1, 5) as $p) <td class="px-1 py-1.5 text-center font-mono">{{ ($b['pertemuan'][$p] ?? 0) ?: '—' }}</td> @endforeach
                        <td class="px-2 py-1.5 font-mono">{{ $b['posttest'] ?: '—' }}</td>
                        <td class="px-2 py-1.5 font-mono">{{ $b['angket'] ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="px-3 py-4 text-tinta-3">Belum ada kelas berkelompok riset (eksperimen/kontrol/uji terbatas). Atur di panel admin → Kelas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach ($baris as $b)
        @foreach ($b['peringatan'] as $p)
            <p class="mt-2 rounded-lg border border-peringatan/40 bg-aksen-latar px-3 py-2 text-xs text-peringatan"><b>Perhatian.</b> {{ $b['kelas']->nama }} {{ $b['kelas']->school?->nama }}: {{ $p }}</p>
        @endforeach
    @endforeach
</div>
