<div>
    <x-peneliti-nav aktif="ekspor" />
    <h1 class="mt-3 text-xl font-bold">Ekspor data</h1>
    <p class="text-sm text-tinta-2">Satu berkas .xlsx: siswa, ct_pretest, ct_posttest, ngain, mastery, adaptasi, motif, angket_siswa, angket_guru, validasi_ahli, observasi, refleksi. <b>Selalu kode anonim</b> (S-001, …); hanya siswa yang bersedia diteliti.</p>

    @if (! $anonim)
        <p class="mt-3 rounded-lg border-2 border-danger-500 bg-red-50 px-3 py-2 text-sm text-red-800">anonimkan_ekspor = false — ekspor dinonaktifkan. Kembalikan ke true di config/geulis.php.</p>
    @endif

    <button type="button" wire:click="jalankan" wire:loading.attr="disabled" class="mt-3 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white disabled:opacity-60" @disabled(! $anonim)>
        <span wire:loading.remove wire:target="jalankan">Buat ekspor sekarang</span><span wire:loading wire:target="jalankan">Menyusun berkas…</span>
    </button>
    @if ($pesan) <p class="mt-2 text-sm text-tinta-2">{{ $pesan }}</p> @endif

    <ul class="mt-4 divide-y divide-garis-2 rounded-xl border border-garis bg-white text-xs">
        @forelse ($riwayat as $r)
            <li class="flex items-center justify-between gap-2 px-3 py-2" wire:key="e-{{ $r->id }}">
                <div><div class="font-mono">{{ basename($r->berkas) }}</div><div class="text-tinta-3">{{ $r->created_at->format('d/m/Y H:i') }} · {{ $r->pembuat?->nama }} · {{ count($r->parameter['lembar'] ?? []) }} lembar</div></div>
                <a href="{{ route('riset.ekspor.unduh', $r) }}" class="shrink-0 rounded-lg border border-aksen px-3 py-1.5 font-semibold text-aksen">Unduh</a>
            </li>
        @empty
            <li class="px-3 py-3 text-tinta-3">Belum ada ekspor.</li>
        @endforelse
    </ul>
</div>
