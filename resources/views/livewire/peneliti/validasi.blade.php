<div>
    <x-peneliti-nav aktif="validasi" />
    <h1 class="mt-3 text-xl font-bold">Validasi ahli</h1>

    <section class="mt-4 rounded-xl border border-garis bg-white p-3">
        <h2 class="text-sm font-semibold">Undang validator</h2>
        <form wire:submit="undang" class="mt-2 space-y-2">
            <select wire:model="instrumenId" class="block w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm">
                @foreach ($daftarInstrumen as $i) <option value="{{ $i->id }}">{{ $i->nama }}</option> @endforeach
            </select>
            <input type="text" wire:model="namaValidator" placeholder="Nama validator" class="block w-full rounded-lg border border-garis px-3 py-2 text-sm">
            @error('namaValidator') <p class="text-xs text-peringatan">{{ $message }}</p> @enderror
            <input type="email" wire:model="emailValidator" placeholder="Surel (opsional)" class="block w-full rounded-lg border border-garis px-3 py-2 text-sm">
            <button type="submit" class="block w-full rounded-lg bg-aksen px-4 py-2 text-sm font-semibold text-white">Buat tautan undangan</button>
        </form>
        <ul class="mt-3 space-y-2 text-xs">
            @foreach ($undangan as $u)
                <li class="rounded-lg border border-garis-2 p-2" wire:key="u-{{ $u->id }}">
                    <div class="flex items-center justify-between"><b>{{ $u->validator->nama }}</b><span class="rounded-full px-2 py-0.5 {{ $u->status === 'selesai' ? 'bg-green-50 text-ok' : 'bg-aksen-latar text-aksen' }}">{{ $u->status }}</span></div>
                    <input type="text" readonly value="{{ \App\Livewire\Peneliti\Validasi::tautan($u) }}" onclick="this.select()" class="mt-1 block w-full rounded border border-garis-2 bg-latar px-2 py-1 font-mono text-[10px]">
                </li>
            @endforeach
        </ul>
    </section>

    @if ($rekap)
        <section class="mt-4">
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-xl border border-garis bg-white p-3"><div class="text-2xl font-bold text-aksen">{{ number_format($rekap['keseluruhan']['nilai_v'], 2, ',') }}</div><div class="text-[10px] text-tinta-3">V keseluruhan · {{ $rekap['keseluruhan']['kategori'] }}</div></div>
                <div class="rounded-xl border border-garis bg-white p-3"><div class="text-2xl font-bold">{{ $rekap['memenuhi'] }}/{{ $rekap['butir']->count() }}</div><div class="text-[10px] text-tinta-3">butir memenuhi</div></div>
                <div class="rounded-xl border border-garis bg-white p-3"><div class="text-2xl font-bold {{ $rekap['perlu_revisi'] ? 'text-peringatan' : '' }}">{{ $rekap['perlu_revisi'] }}</div><div class="text-[10px] text-tinta-3">perlu revisi</div></div>
            </div>
            <p class="mt-2 text-xs text-tinta-3">{{ $rekap['n_penilai'] }} penilai · skala 1–{{ $instrumen->skala_maks }} · V = Σ(r − 1) / [n × ({{ $instrumen->skala_maks }} − 1)] · valid bila minimal <b>sedang</b>.</p>

            <table class="mt-3 w-full text-xs">
                <thead class="text-left text-tinta-3"><tr><th class="py-1">Aspek</th><th>Butir</th><th>V</th><th>Kategori</th></tr></thead>
                <tbody>
                    @foreach ($rekap['aspek'] as $nama => $a)
                        <tr class="border-t border-garis-2"><td class="py-1.5">{{ $nama }}</td><td>{{ $a['n_butir'] }}</td><td class="font-mono">{{ number_format($a['nilai_v'], 2, ',') }}</td><td class="capitalize">{{ $a['kategori'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>

            <h3 class="mt-4 text-sm font-semibold">Per butir</h3>
            <ul class="mt-2 space-y-1 text-xs">
                @foreach ($rekap['butir'] as $b)
                    <li class="rounded-lg border p-2 {{ $b['kategori'] === 'rendah' ? 'border-peringatan/50 bg-aksen-latar' : 'border-garis-2 bg-white' }}" wire:key="b-{{ $b['item']->id }}">
                        <div class="flex justify-between gap-2"><span>{{ $b['item']->urutan }}. {{ $b['item']->pernyataan }}</span><span class="shrink-0 font-mono">{{ $b['v'] === null ? '–' : number_format($b['v'], 2, ',') }} · {{ $b['kategori'] ?? '–' }}</span></div>
                        @foreach ($b['saran'] as $s) <div class="mt-1 text-tinta-2">↳ {{ $s }}</div> @endforeach
                    </li>
                @endforeach
            </ul>

            <a href="{{ route('riset.saran') }}" class="mt-4 block rounded-lg border border-aksen px-4 py-2 text-center text-sm font-semibold text-aksen">Unduh rekap saran sebagai daftar tugas revisi (.csv)</a>
        </section>
    @else
        <p class="mt-4 rounded-xl border border-dashed border-garis p-4 text-sm text-tinta-3">Rekap Aiken's V muncul setelah setidaknya satu validator mengirim lembar.</p>
    @endif
</div>
