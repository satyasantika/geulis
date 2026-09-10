<div>
    @php
        $urutanTahap = ['kesiapan' => 1, 'profil' => 2, 'minat' => 3, 'selesai' => 3];
    @endphp
    <div class="mb-4 flex items-center gap-2 text-xs text-tinta-3">
        @foreach (['Tes kesiapan', 'Profil belajar', 'Minat budaya'] as $i => $nama)
            <span class="{{ $urutanTahap[$tahap] >= $i + 1 ? 'font-semibold text-aksen' : '' }}">{{ $i + 1 }}. {{ $nama }}</span>
            @if ($i < 2) <span>›</span> @endif
        @endforeach
    </div>

    @if ($tahap === 'kesiapan' && $butirKesiapan)
        <h1 class="text-lg font-bold">Tes Kesiapan Prasyarat</h1>
        <p class="mt-1 text-sm text-tinta-2">Butir {{ $nomorKesiapan }} dari {{ $totalKesiapan }}. Bukan nilai rapor — ini untuk memilih titik mulai yang pas buatmu.</p>
        <div class="mt-3 h-1.5 w-full rounded-full bg-garis-2"><div class="h-1.5 rounded-full bg-aksen" style="width: {{ ($nomorKesiapan - 1) / max(1, $totalKesiapan) * 100 }}%"></div></div>

        <form wire:submit="jawabKesiapan" class="mt-5">
            <p class="text-base font-medium leading-relaxed">{{ $butirKesiapan->pertanyaan }}</p>
            <div class="mt-4 space-y-2">
                @foreach ($butirKesiapan->pilihan as $i => $teks)
                    <label class="flex items-center gap-3 rounded-xl border border-garis bg-white p-3 has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar">
                        <input type="radio" wire:model="jawaban" value="{{ $i }}" name="jawaban">
                        <span class="text-base">{{ $teks }}</span>
                    </label>
                @endforeach
            </div>
            @error('jawaban') <p class="mt-2 text-sm text-peringatan">{{ $message }}</p> @enderror
            <button type="submit" class="mt-5 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Lanjut</button>
        </form>

    @elseif (in_array($tahap, ['profil', 'minat'], true))
        @php
            $model = $tahap === 'profil' ? 'jawabanProfil' : 'jawabanMinat';
            $awal = $halaman * $this->butirPerHalaman();
            $potongan = $butirAngket->slice($awal, $this->butirPerHalaman());
        @endphp
        <h1 class="text-lg font-bold">{{ $tahap === 'profil' ? 'Angket Profil Belajar' : 'Angket Minat Konteks Budaya' }}</h1>
        <p class="mt-1 text-sm text-tinta-2">Tidak ada jawaban benar atau salah. Pilih yang paling menggambarkan dirimu.</p>
        <div class="mt-3 h-1.5 w-full rounded-full bg-garis-2"><div class="h-1.5 rounded-full bg-aksen" style="width: {{ $awal / max(1, $butirAngket->count()) * 100 }}%"></div></div>

        <div class="mt-4 space-y-4">
            @foreach ($potongan as $i => $butir)
                <fieldset class="rounded-xl border border-garis bg-white p-3" wire:key="{{ $tahap }}-{{ $i }}">
                    <legend class="sr-only">Pernyataan {{ $i + 1 }}</legend>
                    <p class="text-sm font-medium leading-relaxed">{{ $i + 1 }}. {{ $butir['teks'] }}</p>
                    <div class="mt-2 grid grid-cols-4 gap-1">
                        @foreach ($skala as $nilai => $label)
                            <label class="flex flex-col items-center rounded-lg border border-garis-2 px-1 py-2 text-center has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar">
                                <input type="radio" wire:model.live="{{ $model }}.{{ $i }}" value="{{ $nilai }}" class="sr-only">
                                <span class="text-base font-bold">{{ $nilai }}</span>
                                <span class="text-[10px] leading-tight text-tinta-3">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>
        @error('angket') <p class="mt-2 text-sm text-peringatan">{{ $message }}</p> @enderror
        <div class="mt-5 flex gap-2">
            @if ($halaman > 0)
                <button type="button" wire:click="halamanSebelumnya" class="rounded-lg border border-garis px-4 py-3 text-tinta-2">Kembali</button>
            @endif
            <button type="button" wire:click="halamanBerikutnya" class="flex-1 rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Lanjut</button>
        </div>

    @elseif ($tahap === 'selesai')
        <h1 class="text-lg font-bold">Semua sudah terisi</h1>
        <p class="mt-2 text-sm text-tinta-2">Sistem akan memilih titik mulai dan cara penyajian yang paling pas buatmu. Kamu tetap akan menemui semua cara belajar sepanjang lima pertemuan.</p>
        <button type="button" wire:click="selesai" class="mt-5 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white" wire:loading.attr="disabled">
            Lihat jalur belajarku
        </button>
    @endif
</div>
