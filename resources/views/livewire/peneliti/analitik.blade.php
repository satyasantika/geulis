<div>
    <x-peneliti-nav aktif="analitik" />
    <h1 class="mt-3 text-xl font-bold">Analitik penelitian</h1>
    <p class="text-sm text-tinta-2">N-Gain tes CT · hanya siswa yang bersedia diteliti dan sudah pretest + posttest ({{ $baris->count() }} siswa).</p>

    <div class="mt-3 grid grid-cols-2 gap-2">
        @foreach (['eksperimen', 'kontrol'] as $k)
            @php $g = $kelompok[$k] ?? null; @endphp
            <div class="rounded-xl border border-garis bg-white p-3">
                <div class="text-xs capitalize text-tinta-3">{{ $k }} (n = {{ $g['n'] ?? 0 }})</div>
                <div class="text-2xl font-bold text-aksen">{{ $g && $g['rerata'] !== null ? number_format($g['rerata'], 2, ',') : '–' }}</div>
                <div class="text-[10px] text-tinta-3">N-Gain rerata · SD {{ $g && $g['sd'] !== null ? number_format($g['sd'], 2, ',') : '–' }} · {{ $g['kategori'] ?? '–' }}</div>
            </div>
        @endforeach
    </div>

    <h2 class="mt-4 text-sm font-semibold">Per indikator CT</h2>
    <div class="mt-1 overflow-x-auto rounded-xl border border-garis bg-white">
        <table class="w-full text-xs">
            <thead class="bg-latar text-left"><tr><th class="px-2 py-2">Indikator</th><th class="px-2 py-2">Pre (E)</th><th class="px-2 py-2">Post (E)</th><th class="px-2 py-2">N-Gain E</th><th class="px-2 py-2">N-Gain K</th></tr></thead>
            <tbody>
                @foreach ($indikator as $kode => $i)
                    <tr class="border-t border-garis-2"><td class="px-2 py-1.5">{{ $labelIndikator[$kode] }}</td>
                        @foreach (['pre_e', 'post_e', 'ngain_e', 'ngain_k'] as $kol) <td class="px-2 py-1.5 font-mono">{{ $i[$kol] === null ? '–' : number_format($i[$kol], $kol[0] === 'n' ? 2 : 1, ',') }}</td> @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2 class="mt-4 text-sm font-semibold">Per kelas</h2>
    <ul class="mt-1 space-y-1 text-xs">
        @forelse ($kelas as $nama => $g)
            <li class="flex justify-between rounded-lg border border-garis-2 bg-white px-3 py-1.5"><span>{{ $nama }} (n = {{ $g['n'] }})</span><span class="font-mono">{{ $g['rerata'] === null ? '–' : number_format($g['rerata'], 2, ',') }} · {{ $g['kategori'] ?? '–' }} · min {{ $g['min'] ?? '–' }} · maks {{ $g['maks'] ?? '–' }}</span></li>
        @empty
            <li class="text-tinta-3">Belum ada pasangan pretest–posttest yang lengkap.</li>
        @endforelse
    </ul>

    <h2 class="mt-4 text-sm font-semibold">Kepraktisan (angket respons)</h2>
    <div class="mt-1 grid grid-cols-2 gap-2">
        @foreach ($angket as $sasaran => $a)
            <div class="rounded-xl border border-garis bg-white p-3 text-xs">
                <div class="capitalize text-tinta-3">{{ $sasaran }} · {{ $a['n_responden'] }} responden</div>
                <div class="text-xl font-bold">{{ $a['rerata'] === null ? '–' : number_format($a['rerata'], 2, ',') }}</div>
                <div class="capitalize text-tinta-3">{{ $a['kategori'] ?? 'belum ada' }}</div>
                @foreach ($a['per_aspek'] as $aspek => $pa) <div class="mt-1 flex justify-between"><span>{{ $aspek }}</span><span class="font-mono">{{ number_format($pa['rerata'], 2, ',') }}</span></div> @endforeach
            </div>
        @endforeach
    </div>

    <p class="mt-4 rounded-lg border border-garis bg-latar px-3 py-2 text-xs text-tinta-2"><b>Uji-t dijalankan di SPSS/JASP, bukan di sistem ini.</b> Sistem hanya menyiapkan data yang bersih dan siap dianalisis — statistik inferensial untuk artikel berasal dari perangkat lunak baku yang dapat diaudit reviewer.</p>
</div>
