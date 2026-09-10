<div>
    <h1 class="text-xl font-bold">Selamat datang, {{ auth()->user()->nama }}</h1>
    <p class="mt-1 text-sm text-tinta-2">Kelas yang Anda ampu.</p>

    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
        <a href="{{ route('guru.penilaian-uraian') }}" wire:navigate class="rounded-xl border border-garis bg-white p-3 text-center">Penilaian uraian</a>
        <a href="{{ route('guru.pendampingan') }}" wire:navigate class="rounded-xl border border-garis bg-white p-3 text-center">Perlu pendampingan</a>
        @foreach (\App\Models\Meeting::query()->where('terbit', true)->orderBy('urutan')->get() as $mtg)
            <a href="{{ route('guru.penilaian', $mtg) }}" wire:navigate class="rounded-xl border border-garis bg-white p-3 text-center">Nilai produk P{{ $mtg->urutan }}</a>
        @endforeach
    </div>

    <div class="mt-5 space-y-3">
        @forelse ($daftarKelas as $kelas)
            <a href="{{ route('guru.kelas', $kelas) }}" wire:navigate
               class="block rounded-xl border border-garis bg-white p-4 active:bg-aksen-latar">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold">{{ $kelas->nama }}</div>
                        <div class="text-xs text-tinta-3">{{ $kelas->school?->nama }} · {{ $kelas->tahun_ajaran }}</div>
                    </div>
                    <span class="rounded-full bg-aksen-latar px-2 py-1 text-xs text-aksen">{{ $kelas->enrollments_count }} siswa</span>
                </div>
                <div class="mt-2 text-xs text-tinta-2">Kode gabung: <span class="font-mono font-semibold">{{ $kelas->kode_gabung }}</span></div>
            </a>
        @empty
            <p class="rounded-xl border border-dashed border-garis p-4 text-sm text-tinta-3">Belum ada kelas. Buat kelas pertama Anda di bawah.</p>
        @endforelse
    </div>

    @if (! $formTerbuka)
        <button type="button" wire:click="$set('formTerbuka', true)"
                class="mt-5 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">
            Buat kelas baru
        </button>
    @else
        <form wire:submit="simpan" class="mt-5 space-y-3 rounded-xl border border-garis bg-white p-4">
            <h2 class="font-semibold">Kelas baru</h2>
            <div>
                <label for="nama" class="mb-1 block text-sm text-tinta-2">Nama kelas</label>
                <input id="nama" type="text" wire:model="nama" placeholder="XI MIPA 3"
                       class="block w-full rounded-lg border border-garis px-3 py-3 text-base">
                @error('nama') <p class="mt-1 text-xs text-peringatan">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tahun_ajaran" class="mb-1 block text-sm text-tinta-2">Tahun ajaran</label>
                <input id="tahun_ajaran" type="text" wire:model="tahun_ajaran" placeholder="2026/2027"
                       class="block w-full rounded-lg border border-garis px-3 py-3 text-base">
                @error('tahun_ajaran') <p class="mt-1 text-xs text-peringatan">Tulis dalam bentuk 2026/2027.</p> @enderror
            </div>
            <div>
                <label for="school_id" class="mb-1 block text-sm text-tinta-2">Sekolah</label>
                <select id="school_id" wire:model="school_id" class="block w-full rounded-lg border border-garis bg-white px-3 py-3 text-base">
                    <option value="">Pilih sekolah</option>
                    @foreach ($sekolah as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
                @error('school_id') <p class="mt-1 text-xs text-peringatan">Pilih sekolah.</p> @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Simpan</button>
                <button type="button" wire:click="$set('formTerbuka', false)" class="rounded-lg border border-garis px-4 py-3 text-tinta-2">Batal</button>
            </div>
        </form>
    @endif
</div>
