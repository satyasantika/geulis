<div>
    <a href="{{ route('guru.beranda') }}" wire:navigate class="text-sm text-tinta-3">&larr; Beranda</a>
    <h1 class="mt-2 text-xl font-bold">Penilaian produk · Pertemuan {{ $meeting->urutan }}</h1>
    <p class="text-sm text-tinta-2">{{ $produk->count() }} karya · {{ $produk->filter(fn ($p) => $skor[$p->id]['lengkap'])->count() }} sudah dinilai</p>

    <div class="mt-4 space-y-4">
        @forelse ($produk as $p)
            <div class="rounded-xl border border-garis bg-white p-3" wire:key="p-{{ $p->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-sm font-semibold">{{ $p->user->kode_anonim }} · {{ $p->user->nama }}</div>
                        <div class="text-xs text-tinta-3">{{ \App\Models\Product::BENTUK[$p->bentuk]['label'] ?? $p->bentuk }} · {{ $p->dikirim_pada?->format('d/m H:i') }}</div>
                    </div>
                    @if ($skor[$p->id]['skor'] !== null)
                        <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-ok">{{ number_format($skor[$p->id]['skor'], 0) }}/100</span>
                    @endif
                </div>
                <p class="mt-2 whitespace-pre-line text-sm text-tinta-2">{{ $p->deskripsi }}</p>
                <div class="mt-2 flex gap-2 text-xs">
                    @if ($p->berkas) <a href="{{ route('produk.berkas', $p) }}" target="_blank" class="rounded-lg border border-garis px-2 py-1">Buka berkas</a> @endif
                    @if ($p->tautan) <a href="{{ $p->tautan }}" target="_blank" rel="noopener" class="rounded-lg border border-garis px-2 py-1">Buka tautan</a> @endif
                </div>

                <div class="mt-3 space-y-3">
                    @foreach ($rubrik->criteria as $k)
                        <div wire:key="k-{{ $p->id }}-{{ $k->id }}">
                            <div class="text-xs font-semibold">{{ $k->kriteria }} <span class="font-normal text-tinta-3">· {{ $k->bobot }}%</span></div>
                            <div class="mt-1 grid grid-cols-4 gap-1">
                                @foreach ($k->deskriptor as $t => $d)
                                    <label class="rounded-lg border px-1 py-1.5 text-center text-[11px] leading-tight has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar" title="{{ $d }}">
                                        <input type="radio" class="sr-only" wire:model="tingkat.{{ $p->id }}.{{ $k->id }}" value="{{ $t }}"><b>{{ $t }}</b><br><span class="text-tinta-3">{{ \Illuminate\Support\Str::limit($d, 28) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <input type="text" wire:model="umpanBalik.{{ $p->id }}.{{ $k->id }}" placeholder="Umpan balik (opsional)" class="mt-1 block w-full rounded-lg border border-garis px-2 py-1.5 text-xs">
                        </div>
                    @endforeach
                </div>
                @error("tingkat.{$p->id}") <p class="mt-2 text-xs text-peringatan">{{ $message }}</p> @enderror
                <button type="button" wire:click="simpan({{ $p->id }})" class="mt-3 block w-full rounded-lg bg-aksen px-4 py-2 text-sm font-semibold text-white">Simpan penilaian</button>
            </div>
        @empty
            <p class="rounded-xl border border-dashed border-garis p-4 text-sm text-tinta-3">Belum ada karya yang dikirim untuk pertemuan ini.</p>
        @endforelse
    </div>
</div>
