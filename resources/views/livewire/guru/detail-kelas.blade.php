<div>
    <a href="{{ route('guru.beranda') }}" wire:navigate class="text-sm text-tinta-3">&larr; Semua kelas</a>
    <h1 class="mt-2 text-xl font-bold">{{ $kelas->nama }}</h1>
    <p class="text-sm text-tinta-2">{{ $kelas->school?->nama }} · {{ $kelas->tahun_ajaran }} · kode gabung <span class="font-mono font-semibold">{{ $kelas->kode_gabung }}</span></p>

    @foreach ($pesan as $p)
        <p class="mt-3 rounded-lg border border-ok/40 bg-green-50 px-3 py-2 text-sm text-ok">{{ $p }}</p>
    @endforeach
    @if ($galat !== [])
        <ul class="mt-3 space-y-1 rounded-lg border border-peringatan/40 bg-aksen-latar px-3 py-2 text-sm text-peringatan">
            @foreach ($galat as $g) <li>{{ $g }}</li> @endforeach
        </ul>
    @endif

    <section class="mt-5 rounded-xl border border-garis bg-white p-4">
        <h2 class="font-semibold">Impor siswa dari CSV</h2>
        <p class="mt-1 text-xs text-tinta-3">Kolom: <code>nama, nis, jenis_kelamin</code> (L/P). PIN dibuat otomatis.</p>
        <form wire:submit="impor" class="mt-3 space-y-3">
            <input type="file" wire:model="berkas" accept=".csv,text/csv" class="block w-full text-sm">
            @error('berkas') <p class="text-xs text-peringatan">{{ $message }}</p> @enderror
            <button type="submit" class="block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="impor">Impor</span>
                <span wire:loading wire:target="impor">Memproses…</span>
            </button>
        </form>
    </section>

    <section class="mt-4 rounded-xl border border-garis bg-white p-4">
        <h2 class="font-semibold">Tambah satu siswa</h2>
        <form wire:submit="tambahSatu" class="mt-3 space-y-3">
            <input type="text" wire:model="namaBaru" placeholder="Nama" class="block w-full rounded-lg border border-garis px-3 py-3 text-base">
            @error('namaBaru') <p class="text-xs text-peringatan">{{ $message }}</p> @enderror
            <input type="text" inputmode="numeric" wire:model="nisBaru" placeholder="NIS" class="block w-full rounded-lg border border-garis px-3 py-3 text-base">
            @error('nisBaru') <p class="text-xs text-peringatan">{{ $message }}</p> @enderror
            <select wire:model="jkBaru" class="block w-full rounded-lg border border-garis bg-white px-3 py-3 text-base">
                <option value="">Jenis kelamin (opsional)</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
            <button type="submit" class="block w-full rounded-lg border border-aksen px-4 py-3 font-semibold text-aksen">Tambahkan</button>
        </form>
    </section>

    <section class="mt-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold">Siswa ({{ $siswa->count() }})</h2>
            @if ($siswa->isNotEmpty())
                <a href="{{ route('guru.kartu-pin', $kelas) }}" target="_blank"
                   class="rounded-lg bg-aksen px-3 py-2 text-sm font-semibold text-white">Cetak kartu PIN</a>
            @endif
        </div>

        <ul class="mt-3 divide-y divide-garis-2 rounded-xl border border-garis bg-white">
            @forelse ($siswa as $s)
                <li class="p-3" wire:key="siswa-{{ $s->id }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="truncate font-medium">{{ $s->nama }}</div>
                            <div class="text-xs text-tinta-3">NIS {{ $s->username }} · {{ $s->kode_anonim }}</div>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <button type="button" wire:click="aturUlangPin({{ $s->id }})" wire:confirm="Atur ulang PIN {{ $s->nama }}? PIN lama tidak berlaku lagi."
                                    class="rounded-lg border border-garis px-2 py-1 text-xs text-tinta-2">PIN baru</button>
                            <button type="button" wire:click="keluarkan({{ $s->id }})" wire:confirm="Keluarkan {{ $s->nama }} dari kelas ini?"
                                    class="rounded-lg border border-garis px-2 py-1 text-xs text-tinta-3">Keluarkan</button>
                        </div>
                    </div>
                    @if (isset($pinBaru[$s->id]))
                        <p class="mt-2 rounded bg-aksen-latar px-2 py-1 text-sm">PIN baru: <span class="font-mono text-lg font-bold tracking-widest">{{ $pinBaru[$s->id] }}</span></p>
                    @endif
                </li>
            @empty
                <li class="p-4 text-sm text-tinta-3">Belum ada siswa. Impor dari CSV atau tambahkan satu per satu.</li>
            @endforelse
        </ul>
    </section>
</div>
