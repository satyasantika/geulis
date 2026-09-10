<div>
    <div class="text-xs text-tinta-3">GEULIS · validasi ahli</div>
    <h1 class="text-lg font-bold leading-snug">{{ $instrumen->nama }}</h1>
    <p class="mt-1 text-sm text-tinta-2">{{ $validasi->validator->nama }} · Aspek {{ $aspekKe + 1 }} dari {{ count($aspek) }} · {{ $terisi }} dari {{ $total }} butir terisi</p>
    <div class="mt-2 h-1.5 w-full rounded-full bg-garis-2"><div class="h-1.5 rounded-full bg-aksen" style="width: {{ $total ? $terisi / $total * 100 : 0 }}%"></div></div>

    @if ($selesai)
        <div class="mt-4 rounded-xl border border-ok/50 bg-green-50 p-4">
            <p class="font-semibold text-ok">Terima kasih. Lembar Anda sudah terkirim{{ $validasi->selesai_pada ? ' pada '.$validasi->selesai_pada->translatedFormat('j F Y H:i') : '' }}.</p>
            <p class="mt-1 text-sm text-tinta-2">Skor dan saran Anda langsung diolah menjadi indeks Aiken's V dan daftar revisi oleh tim peneliti.</p>
        </div>
    @else
        <h2 class="mt-5 text-sm font-semibold text-aksen">{{ $namaAspek }}</h2>
        <p class="text-xs text-tinta-3">Skala 1 (sangat tidak sesuai) – {{ $instrumen->skala_maks }} (sangat sesuai). Jawaban tersimpan otomatis.</p>

        <div class="mt-3 space-y-4">
            @foreach ($butir as $b)
                <div class="rounded-xl border border-garis bg-white p-3" wire:key="butir-{{ $b->id }}">
                    <p class="text-sm font-medium leading-relaxed">{{ $b->urutan }}. {{ $b->pernyataan }}</p>
                    <div class="mt-2 grid gap-1" style="grid-template-columns: repeat({{ $instrumen->skala_maks }}, minmax(0, 1fr));">
                        @foreach (range(1, $instrumen->skala_maks) as $s)
                            <label class="rounded-lg border px-1 py-2 text-center text-sm has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar has-[:checked]:font-bold">
                                <input type="radio" class="sr-only" wire:model.live="skor.{{ $b->id }}" value="{{ $s }}">{{ $s }}
                            </label>
                        @endforeach
                    </div>
                    <input type="text" wire:model.blur="saran.{{ $b->id }}" placeholder="Saran perbaikan (opsional)" class="mt-2 block w-full rounded-lg border border-garis px-3 py-2 text-sm">
                </div>
            @endforeach
        </div>

        @error('kirim') <p class="mt-3 rounded-lg border border-peringatan/40 bg-aksen-latar px-3 py-2 text-sm text-peringatan">{{ $message }}</p> @enderror

        <div class="mt-4 flex gap-2">
            @if ($aspekKe > 0)
                <button type="button" wire:click="aspekSebelumnya" class="rounded-lg border border-garis px-4 py-3 text-tinta-2">Sebelumnya</button>
            @endif
            @if ($aspekKe < count($aspek) - 1)
                <button type="button" wire:click="aspekBerikutnya" class="flex-1 rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Aspek berikutnya</button>
            @else
                <button type="button" wire:click="kirim" wire:confirm="Kirim lembar validasi? Setelah dikirim, skor tidak bisa diubah." class="flex-1 rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Kirim lembar</button>
            @endif
        </div>
    @endif
</div>
