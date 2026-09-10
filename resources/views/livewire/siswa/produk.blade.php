<div>
    <a href="{{ route('siswa.jalur') }}" wire:navigate class="text-sm text-tinta-3">&larr; Jalur belajar</a>
    <h1 class="mt-2 text-xl font-bold">Proyek Akhir · Pertemuan {{ $meeting->urutan }}</h1>
    <p class="mt-1 text-sm text-tinta-2">Konten ditentukan sistem, cara belajar dari profilmu, <b>bentuk karya kamu yang pilih</b>.</p>

    @if ($produk?->rubricScores()->exists())
        <p class="mt-3 rounded-lg border border-ok/40 bg-green-50 px-3 py-2 text-sm text-ok">Karyamu sudah dinilai. Lihat umpan baliknya di Kemajuanku.</p>
    @endif

    <form wire:submit="kirim" class="mt-4 space-y-4">
        <div class="grid grid-cols-2 gap-2">
            @foreach ($daftarBentuk as $kode => $b)
                <label class="rounded-xl border p-3 has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar {{ $bentuk === $kode ? 'border-aksen bg-aksen-latar' : 'border-garis bg-white' }}" wire:key="bentuk-{{ $kode }}">
                    <input type="radio" wire:model.live="bentuk" value="{{ $kode }}" class="sr-only">
                    <div class="text-xl">{{ $b['ikon'] }}</div>
                    <div class="mt-1 text-sm font-semibold leading-tight">{{ $b['label'] }}</div>
                    <div class="mt-1 text-[11px] leading-snug text-tinta-2">{{ $b['keterangan'] }}</div>
                </label>
            @endforeach
        </div>
        @error('bentuk') <p class="text-sm text-peringatan">{{ $message }}</p> @enderror

        @if ($rubrik)
            <details class="rounded-xl border border-garis bg-white p-3" open>
                <summary class="cursor-pointer text-sm font-semibold">Rubrik penilaian (baca sebelum mengerjakan)</summary>
                <p class="mt-1 text-xs text-tinta-3">Keempat bentuk dinilai dengan rubrik yang sama, empat tingkat.</p>
                <div class="mt-2 space-y-2">
                    @foreach ($rubrik->criteria as $k)
                        <div class="text-xs">
                            <div class="font-semibold">{{ $k->kriteria }} <span class="font-normal text-tinta-3">· {{ $k->bobot }}%</span></div>
                            <ol class="mt-0.5 space-y-0.5 text-tinta-2">
                                @foreach ($k->deskriptor as $t => $d) <li><b>{{ $t }}</b> {{ $d }}</li> @endforeach
                            </ol>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif

        <div>
            <label class="mb-1 block text-sm text-tinta-2">Berkas (gambar/PDF, maks 2 MB)</label>
            <input type="file" wire:model="berkas" accept=".png,.jpg,.jpeg,.webp,.pdf,.svg" class="block w-full text-sm">
            @if ($produk?->berkas) <p class="mt-1 text-xs text-tinta-3">Sudah ada berkas: {{ basename($produk->berkas) }} — unggah lagi untuk mengganti.</p> @endif
            @error('berkas') <p class="mt-1 text-xs text-peringatan">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm text-tinta-2">Tautan (untuk video: YouTube/Drive)</label>
            <input type="url" wire:model="tautan" placeholder="https://…" class="block w-full rounded-lg border border-garis px-3 py-2 text-base">
            @error('tautan') <p class="mt-1 text-xs text-peringatan">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm text-tinta-2">Ceritakan karyamu: artefak apa, transformasi apa, algoritmanya bagaimana</label>
            <textarea wire:model="deskripsi" rows="5" class="block w-full rounded-lg border border-garis px-3 py-2 text-base"></textarea>
            @error('deskripsi') <p class="mt-1 text-xs text-peringatan">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white" wire:loading.attr="disabled">
            {{ $produk ? 'Perbarui karya' : 'Kirim karya' }}
        </button>
    </form>
</div>
