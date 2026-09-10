<div>
    <a href="{{ route('siswa.jalur') }}" wire:navigate class="text-sm text-tinta-3">&larr; Jalur belajar</a>
    <h1 class="mt-2 text-lg font-bold leading-snug">{{ $tes->judul }}</h1>

    @if ($selesai)
        <div class="mt-4 rounded-xl border border-ok/50 bg-green-50 p-4">
            <p class="font-semibold text-ok">Jawabanmu sudah terkumpul.</p>
            <p class="mt-1 text-sm text-tinta-2">Soal pilihan ganda dinilai otomatis; soal uraian dinilai gurumu. Hasilnya muncul di Kemajuanku.</p>
        </div>
        <a href="{{ route('siswa.jalur') }}" wire:navigate class="mt-4 block rounded-lg bg-aksen px-4 py-3 text-center font-semibold text-white">Kembali</a>
    @else
        <div
            wire:ignore
            x-data="{
                kunci: @js($kunciLokal),
                sisa: @js($sisaDetik),
                jawaban: @js((object) $jawaban),
                antrean: {},
                daring: navigator.onLine,
                mengumpulkan: false,
                init() {
                    // localStorage menang atas server: itu jawaban terbaru yang mungkin belum sempat terkirim.
                    try {
                        const lokal = JSON.parse(localStorage.getItem(this.kunci) || '{}');
                        this.jawaban = { ...this.jawaban, ...lokal };
                        this.antrean = { ...lokal };
                    } catch (e) {}
                    this.kirimAntrean();
                    window.addEventListener('online', () => { this.daring = true; this.kirimAntrean(); });
                    window.addEventListener('offline', () => { this.daring = false; });
                    const t = setInterval(() => {
                        if (this.sisa <= 0) { clearInterval(t); this.kumpulkan(); return; }
                        this.sisa--;
                    }, 1000);
                },
                simpanLokal() { try { localStorage.setItem(this.kunci, JSON.stringify(this.jawaban)); } catch (e) {} },
                ubah(id, nilai) {
                    this.jawaban[id] = nilai;
                    this.antrean[id] = nilai;
                    this.simpanLokal();
                    this.kirimAntrean();
                },
                async kirimAntrean() {
                    if (!navigator.onLine) return;
                    for (const id of Object.keys(this.antrean)) {
                        const nilai = this.antrean[id];
                        try {
                            await $wire.simpanJawaban(Number(id), nilai);
                            if (this.antrean[id] === nilai) delete this.antrean[id];
                        } catch (e) { break; }
                    }
                },
                async kumpulkan() {
                    if (this.mengumpulkan) return;
                    this.mengumpulkan = true;
                    try {
                        await $wire.kumpulkan(this.jawaban);
                        try { localStorage.removeItem(this.kunci); } catch (e) {}
                    } catch (e) {
                        this.mengumpulkan = false;
                        alert('Koneksi terputus. Jawabanmu tersimpan di HP ini — tekan Kumpulkan lagi begitu sinyal kembali.');
                    }
                },
                format(s) { const m = Math.floor(s / 60), d = s % 60; return m + ':' + String(d).padStart(2, '0'); },
                terjawab() { return Object.values(this.jawaban).filter(v => v !== null && String(v).trim() !== '').length; },
            }"
        >
            <div class="sticky top-0 z-10 mt-3 flex items-center justify-between rounded-lg border border-garis bg-white px-3 py-2 text-sm">
                <span>Sisa waktu <b class="font-mono" :class="sisa < 300 ? 'text-peringatan' : ''" x-text="format(sisa)"></b></span>
                <span class="text-xs text-tinta-3"><span x-text="terjawab()"></span>/{{ $butir->count() }} terjawab
                    <span x-show="!daring" class="ml-1 rounded bg-aksen-latar px-1 text-peringatan">luring — tersimpan di HP</span>
                    <span x-show="daring && Object.keys(antrean).length" class="ml-1 text-tinta-3">menyinkronkan…</span>
                </span>
            </div>

            <ol class="mt-4 space-y-4">
                @foreach ($butir as $b)
                    <li class="rounded-xl border border-garis bg-white p-3" wire:key="ct-{{ $b->id }}">
                        <div class="text-[10px] uppercase tracking-wide text-tinta-3">Soal {{ $b->urutan }} · {{ $indikator[$b->indikator] ?? $b->indikator }}</div>
                        @if ($b->stimulus)
                            <p class="mt-1 rounded-lg bg-latar p-2 text-sm text-tinta-2">{{ $b->stimulus }}</p>
                        @endif
                        <p class="mt-2 text-sm font-medium leading-relaxed">{{ $b->pertanyaan }}</p>

                        @if ($b->tipe === 'pg')
                            <div class="mt-2 space-y-1">
                                @foreach ($b->pilihan as $teks)
                                    <label class="flex items-center gap-2 rounded-lg border border-garis-2 px-3 py-2 has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar">
                                        <input type="radio" name="butir-{{ $b->id }}" value="{{ $teks }}" :checked="jawaban[{{ $b->id }}] === @js($teks)" @change="ubah({{ $b->id }}, @js($teks))">
                                        <span class="text-sm">{{ $teks }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($b->tipe === 'isian')
                            <input type="text" class="mt-2 block w-full rounded-lg border border-garis px-3 py-2 text-base" :value="jawaban[{{ $b->id }}] || ''" @input.debounce.600ms="ubah({{ $b->id }}, $event.target.value)">
                        @else
                            <textarea rows="4" class="mt-2 block w-full rounded-lg border border-garis px-3 py-2 text-base" :value="jawaban[{{ $b->id }}] || ''" @input.debounce.800ms="ubah({{ $b->id }}, $event.target.value)" placeholder="Tulis jawabanmu…"></textarea>
                        @endif
                    </li>
                @endforeach
            </ol>

            <button type="button" @click="if (confirm('Kumpulkan sekarang? Jawaban tidak bisa diubah lagi.')) kumpulkan()" :disabled="mengumpulkan"
                    class="mt-5 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white disabled:opacity-60">
                <span x-show="!mengumpulkan">Kumpulkan</span><span x-show="mengumpulkan">Mengirim…</span>
            </button>
            <p class="mt-2 text-center text-xs text-tinta-3">Jawaban tersimpan otomatis di HP ini dan di server. Kalau sinyal hilang, lanjutkan saja — kirim ulang berjalan sendiri.</p>
        </div>
    @endif
</div>
