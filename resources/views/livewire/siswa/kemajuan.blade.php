<div>
    <a href="{{ route('siswa.jalur') }}" wire:navigate class="text-sm text-tinta-3">&larr; Jalur belajar</a>
    <h1 class="mt-2 text-xl font-bold">Kemajuanku</h1>

    <section class="mt-4 rounded-xl border border-garis bg-white p-4">
        <h2 class="text-sm font-semibold">Empat kemampuan berpikir komputasional</h2>
        @if ($ct)
            <p class="text-xs text-tinta-3">Dari {{ $jenisTes === 'posttest' ? 'tes akhir' : 'tes awal' }}{{ $ctLengkap ? '' : ' · uraian masih dinilai guru' }}</p>
            <div class="mt-3 space-y-2">
                @foreach ($ct as $k)
                    <div>
                        <div class="flex justify-between text-xs"><span>{{ $k['label'] }}</span><span class="font-mono">{{ $k['persen'] ?? '–' }}</span></div>
                        <div class="mt-1 h-2 rounded-full bg-garis-2"><div class="h-2 rounded-full bg-aksen" style="width: {{ $k['persen'] ?? 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-1 text-xs text-tinta-3">Muncul setelah kamu mengerjakan tes CT.</p>
        @endif
    </section>

    <section class="mt-4 rounded-xl border border-garis bg-white p-4">
        <h2 class="text-sm font-semibold">Penguasaan per pertemuan</h2>
        <p class="text-xs text-tinta-3">Nilai M pada pemeriksaan penguasaan · titik mulai sekarang: {{ $labelLevel[$level] ?? '–' }}</p>
        @php
            $titik = $penguasaan->values();
            $n = max(1, $titik->count() - 1);
            $koordinat = $titik->map(fn ($p, $i) => $p['m'] === null ? null : [round($i / $n * 100, 1), round(100 - $p['m'] * 100, 1)]);
            $garis = $koordinat->filter()->map(fn ($c) => implode(',', $c))->implode(' ');
        @endphp
        <svg viewBox="-6 -6 112 112" class="mt-3 w-full" style="max-height: 180px" role="img" aria-label="Grafik penguasaan">
            @foreach ([0.5, 0.8] as $ambang)
                <line x1="0" x2="100" y1="{{ 100 - $ambang * 100 }}" y2="{{ 100 - $ambang * 100 }}" stroke="#e7e5e4" stroke-dasharray="2 2" stroke-width=".8"/>
            @endforeach
            @if ($garis !== '')
                <polyline points="{{ $garis }}" fill="none" stroke="#7c2d12" stroke-width="1.5"/>
            @endif
            @foreach ($koordinat as $i => $c)
                @if ($c)
                    <circle cx="{{ $c[0] }}" cy="{{ $c[1] }}" r="2.2" fill="#c2410c"/>
                @endif
                <text x="{{ round($i / $n * 100, 1) }}" y="108" font-size="5" text-anchor="middle" fill="#a8a29e">{{ $titik[$i]['label'] }}</text>
            @endforeach
        </svg>
    </section>

    <section class="mt-4">
        <h2 class="text-sm font-semibold">Karyamu</h2>
        <p class="text-xs text-tinta-3">Semua motif yang kamu rancang tersimpan. Pada Pertemuan 5 kamu memilih satu untuk proyek akhir.</p>
        <div class="mt-2 grid grid-cols-3 gap-2">
            @forelse ($motif as $k)
                <div class="rounded-xl border border-garis bg-white p-1 text-center" wire:key="motif-{{ $k->id }}">
                    <div class="aspect-square w-full overflow-hidden rounded-lg bg-latar">{!! $k->cuplikan_svg ?? '' !!}</div>
                    <div class="mt-1 text-[10px] text-tinta-2">P{{ $k->activity->lessonUnit->meeting->urutan }} · {{ number_format($k->skor_kemiripan, 0) }}%{{ $k->lolos ? ' ✓' : '' }}</div>
                </div>
            @empty
                <p class="col-span-3 rounded-xl border border-dashed border-garis p-3 text-xs text-tinta-3">Belum ada. Motif pertamamu lahir di Pertemuan 1.</p>
            @endforelse
        </div>
    </section>

    @if ($produk->isNotEmpty())
        <section class="mt-4 space-y-2">
            <h2 class="text-sm font-semibold">Proyek akhir</h2>
            @foreach ($produk as $p)
                <div class="rounded-xl border border-garis bg-white p-3 text-sm" wire:key="produk-{{ $p['produk']->id }}">
                    <div class="font-semibold">{{ \App\Models\Product::BENTUK[$p['produk']->bentuk]['label'] ?? $p['produk']->bentuk }}</div>
                    @if ($p['skor'] !== null)
                        <div class="mt-1 text-xs text-tinta-2">Skor rubrik: <b>{{ number_format($p['skor'], 0) }}</b>/100</div>
                        <ul class="mt-1 space-y-0.5 text-xs text-tinta-2">
                            @foreach ($p['produk']->rubricScores as $s)
                                <li><b>{{ $s->criteria->kriteria }}</b>: tingkat {{ $s->tingkat }}{{ $s->umpan_balik ? ' — '.$s->umpan_balik : '' }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="mt-1 text-xs text-tinta-3">Menunggu penilaian guru.</div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif
</div>
