<div>
    <a href="{{ $kembali }}" wire:navigate class="text-sm text-tinta-3">&larr; Kembali</a>
    <h1 class="mt-2 text-lg font-bold">{{ $angket->nama }}</h1>
    <p class="mt-1 text-sm text-tinta-2">Tidak ada jawaban benar atau salah. Jawablah berdasarkan pengalamanmu selama memakai GEULIS.</p>

    @if ($selesai)
        <div class="mt-4 rounded-xl border border-ok/50 bg-green-50 p-4"><p class="font-semibold text-ok">Terima kasih, angketmu sudah terkirim.</p></div>
    @else
        <div class="mt-4 space-y-3">
            @foreach ($angket->items as $i)
                <fieldset class="rounded-xl border border-garis bg-white p-3" wire:key="q-{{ $i->id }}">
                    <legend class="sr-only">Pernyataan {{ $i->urutan }}</legend>
                    <p class="text-sm font-medium leading-relaxed">{{ $i->urutan }}. {{ $i->pernyataan }}</p>
                    <div class="mt-2 grid gap-1" style="grid-template-columns: repeat({{ count($skala) }}, minmax(0, 1fr));">
                        @foreach ($skala as $nilai => $label)
                            <label class="flex flex-col items-center rounded-lg border border-garis-2 px-1 py-2 text-center has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar">
                                <input type="radio" wire:model.live="jawaban.{{ $i->id }}" value="{{ $nilai }}" class="sr-only">
                                <span class="text-base font-bold">{{ $nilai }}</span>
                                <span class="text-[9px] leading-tight text-tinta-3">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>

        <div class="mt-5 space-y-3">
            <h2 class="text-sm font-bold">Kritik dan Saran <span class="font-normal text-tinta-3">(boleh dikosongkan)</span></h2>
            @foreach ($catatanLabel as $kode => $label)
                <div wire:key="catatan-{{ $kode }}">
                    <label class="text-sm font-medium leading-relaxed" for="catatan-{{ $kode }}">{{ $label }}</label>
                    <textarea id="catatan-{{ $kode }}" wire:model.blur="catatan.{{ $kode }}" rows="2" class="mt-1 w-full rounded-lg border border-garis-2 p-2 text-sm"></textarea>
                </div>
            @endforeach
        </div>

        @error('kirim') <p class="mt-3 text-sm text-peringatan">{{ $message }}</p> @enderror
        <button type="button" wire:click="kirim" class="mt-4 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Kirim angket</button>
    @endif
</div>
