<div>
    <a href="{{ route('siswa.pertemuan', $meeting) }}" wire:navigate class="text-sm text-tinta-3">&larr; Pertemuan {{ $meeting->urutan }}</a>
    <div class="mt-2 flex items-center justify-between gap-2">
        <div class="text-xs text-tinta-3">Bagian {{ $unit->urutan }} dari 7</div>
        <span class="rounded-full bg-aksen-latar px-2 py-0.5 text-xs text-aksen">{{ $level }} · {{ ucfirst($modus) }}</span>
    </div>
    <h1 class="text-xl font-bold">{{ $unit->judul }}</h1>

    {{-- Jangkar budaya: atribusi WAJIB tampil (aturan #6) --}}
    @if ($aset)
        <figure class="mt-4 overflow-hidden rounded-xl border border-garis bg-white">
            @if (str_ends_with($aset->berkas, '.mp4'))
                <video controls playsinline class="w-full" src="{{ asset('storage/'.$aset->berkas) }}"></video>
            @else
                <img src="{{ asset('storage/'.$aset->berkas) }}" alt="{{ $aset->nama_motif }}" class="w-full" loading="lazy">
            @endif
            <figcaption class="p-3 text-xs text-tinta-2">
                <b>{{ $aset->nama_motif }}</b> · dokumentasi lapangan<br>
                <span class="text-tinta-3">Perajin: {{ $aset->atribusi() }}</span>
            </figcaption>
        </figure>
    @endif

    @if ($varian)
        <h2 class="mt-5 font-semibold">{{ $varian->judul }}</h2>
        <div class="prose prose-sm mt-2 max-w-none text-tinta">{!! $varian->badan_konten !!}</div>

        @if (filled($varian->konfigurasi_geogebra['perintah'] ?? null) || $unit->tipe === 'eksplorasi')
            <div class="mt-4" wire:ignore>
                <x-geogebra :konfigurasi="$varian->konfigurasi_geogebra ?? []" id="ggb-unit-{{ $unit->id }}" />
            </div>
        @endif
    @elseif ($unit->tipe !== 'refleksi' && $unit->tipe !== 'motif')
        <p class="mt-5 rounded-xl border border-dashed border-garis p-4 text-sm text-tinta-3">Konten bagian ini belum tersedia untuk level dan cara belajarmu. Beri tahu gurumu, lalu lanjutkan ke bagian berikutnya.</p>
    @endif

    {{-- Kuis: latihan atau pemeriksaan penguasaan --}}
    @if ($kuis && ! $hasil)
        <form wire:submit="kirimKuis" class="mt-6 space-y-4">
            <h2 class="font-semibold">{{ $kuis->judul }}</h2>
            @foreach ($kuis->konfigurasi['butir'] ?? [] as $i => $b)
                <fieldset class="rounded-xl border border-garis bg-white p-3" wire:key="butir-{{ $i }}">
                    <legend class="sr-only">Butir {{ $i + 1 }}</legend>
                    <p class="text-sm font-medium leading-relaxed">{{ $i + 1 }}. {{ $b['pertanyaan'] }}</p>
                    <div class="mt-2 space-y-1">
                        @foreach ($b['pilihan'] as $j => $teks)
                            <label class="flex items-center gap-2 rounded-lg border border-garis-2 px-3 py-2 has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar">
                                <input type="radio" wire:model="jawaban.{{ $i }}" value="{{ $j }}">
                                <span class="text-sm">{{ $teks }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
            @error('jawaban') <p class="text-sm text-peringatan">{{ $message }}</p> @enderror
            <button type="submit" class="block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white" wire:loading.attr="disabled">Periksa jawaban</button>
        </form>
    @endif

    {{-- S-08 umpan balik --}}
    @if ($hasil)
        @php $k = $hasil['keputusan']; @endphp
        <section class="mt-6 rounded-xl border border-garis bg-white p-4">
            @if ($k === null)
                <p class="font-semibold">{{ $hasil['benar'] }} dari {{ $hasil['total'] }} benar</p>
                <p class="mt-1 text-sm text-tinta-2">Latihan tersimpan. Lanjut ke bagian berikutnya kapan pun kamu siap.</p>
            @else
                <div class="text-2xl">
                    @switch($k['kode'])
                        @case('RULE_PROMOTE') ↑ @break
                        @case('RULE_ENRICH') ★ @break
                        @case('RULE_REINFORCE') → @break
                        @case('RULE_REMEDIATE') ↺ @break
                        @case('RULE_ESCALATE') 🤝 @break
                        @default ⏸
                    @endswitch
                </div>
                <p class="mt-1 text-base font-semibold leading-snug">{{ $k['pesan'] }}</p>

                <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1 rounded-lg bg-latar p-3 text-xs text-tinta-2">
                    <dt>Pemeriksaan</dt><dd class="font-mono">{{ $hasil['benar'] }} dari {{ $hasil['total'] }} benar</dd>
                    @if ($k['kode'] !== 'RULE_GUESS_GUARD')
                        <dt>Penguasaan</dt><dd class="font-mono">{{ number_format($k['m_sebelum'], 2, ',') }} → {{ number_format($k['m_sesudah'], 2, ',') }}</dd>
                        <dt>Level</dt><dd class="font-mono">{{ $k['level_sebelum'] }} → {{ $k['level_sesudah'] }}</dd>
                    @endif
                    @if ($k['kode'] === 'RULE_REMEDIATE')
                        <dt>Percobaan ulang</dt><dd class="font-mono">{{ $k['iterasi'] }} dari {{ config('geulis.maks_iterasi_remedial') }}</dd>
                    @endif
                </dl>

                @if ($k['butir_salah'] !== [] && $k['kode'] !== 'RULE_GUESS_GUARD')
                    <p class="mt-3 text-xs font-semibold text-tinta-2">Yang perlu dimantapkan</p>
                    <ul class="mt-1 list-inside list-disc text-xs text-tinta-2">
                        @foreach ($k['butir_salah'] as $s) <li>{{ $s['label'] }}</li> @endforeach
                    </ul>
                @endif

                <p class="mt-3 text-[10px] text-tinta-3">Kode aturan: {{ $k['kode'] }}</p>
            @endif
        </section>

        @if ($k && $k['ulang'])
            <button type="button" wire:click="cobaLagi" class="mt-4 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">
                {{ $k['kode'] === 'RULE_REMEDIATE' ? 'Lihat versi terbimbing, lalu coba lagi' : 'Coba sekali lagi' }}
            </button>
        @endif
    @endif

    {{-- Refleksi --}}
    @if ($unit->tipe === 'refleksi' && ! $selesai)
        <form wire:submit="kirimRefleksi" class="mt-6 space-y-3">
            <h2 class="font-semibold">Refleksi singkat</h2>
            @foreach ($pertanyaanRefleksi as $i => $p)
                <div wire:key="refleksi-{{ $i }}">
                    <label class="mb-1 block text-sm">{{ $p }}</label>
                    <textarea wire:model="refleksi.{{ $i }}" rows="3" class="block w-full rounded-lg border border-garis px-3 py-2 text-base"></textarea>
                </div>
            @endforeach
            @error('refleksi') <p class="text-sm text-peringatan">{{ $message }}</p> @enderror
            <button type="submit" class="block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Kirim refleksi</button>
        </form>
    @endif

    {{-- Motif Builder: Sprint 3 --}}
    @if ($unit->tipe === 'motif' && ! $selesai)
        <div class="mt-6 rounded-xl border border-dashed border-garis p-4 text-sm text-tinta-3">Motif Builder sedang disiapkan. Untuk sementara, lanjutkan ke refleksi.</div>
    @endif

    {{-- Navigasi --}}
    @if ($selesai || (! $kuis && $unit->tipe !== 'refleksi'))
        <div class="mt-6 flex gap-2">
            @if ($selesai && $berikut)
                <a href="{{ route('siswa.unit', $berikut) }}" wire:navigate class="flex-1 rounded-lg bg-aksen px-4 py-3 text-center font-semibold text-white">Lanjut ke {{ $berikut->judul }}</a>
            @elseif ($selesai)
                <a href="{{ route('siswa.pertemuan', $meeting) }}" wire:navigate class="flex-1 rounded-lg bg-aksen px-4 py-3 text-center font-semibold text-white">Kembali ke pertemuan</a>
            @else
                <button type="button" wire:click="selesai" class="flex-1 rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Selesai, lanjut</button>
            @endif
        </div>
    @endif
</div>
