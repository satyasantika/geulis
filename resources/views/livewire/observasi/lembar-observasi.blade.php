<div>
    <h1 class="text-lg font-bold">Lembar observasi</h1>
    <p class="text-sm text-tinta-2">Skor 1 (tidak terlaksana) – 4 (terlaksana sangat baik). Bisa diisi tanpa sinyal; terkirim otomatis begitu daring.</p>

    <div
        wire:ignore
        x-data="{
            kunci: @js($kunciLokal),
            daring: navigator.onLine,
            form: { classroom_id: '', meeting_id: '', tanggal: new Date().toISOString().slice(0, 10), skor: {}, catatan: '' },
            antrean: [],
            pesan: null,
            mengirim: false,
            init() {
                try { this.antrean = JSON.parse(localStorage.getItem(this.kunci) || '[]'); } catch (e) {}
                window.addEventListener('online', () => { this.daring = true; this.kirimAntrean(); });
                window.addEventListener('offline', () => { this.daring = false; });
                this.kirimAntrean();
            },
            simpanAntrean() { try { localStorage.setItem(this.kunci, JSON.stringify(this.antrean)); } catch (e) {} },
            lengkap() { return this.form.classroom_id && this.form.meeting_id && this.form.tanggal && Object.keys(this.form.skor).length >= {{ $butir->count() }}; },
            simpan() {
                if (!this.lengkap()) { this.pesan = 'Isi kelas, pertemuan, tanggal, dan semua butir dulu.'; return; }
                this.antrean.push(JSON.parse(JSON.stringify(this.form)));
                this.simpanAntrean();
                this.form = { classroom_id: this.form.classroom_id, meeting_id: '', tanggal: this.form.tanggal, skor: {}, catatan: '' };
                this.pesan = null;
                this.kirimAntrean();
            },
            async kirimAntrean() {
                if (this.mengirim || !navigator.onLine || !this.antrean.length) return;
                this.mengirim = true;
                try {
                    while (this.antrean.length) {
                        const hasil = await $wire.kirim(this.antrean[0]);
                        if (!hasil.ok) { this.pesan = hasil.pesan; this.antrean.shift(); this.simpanAntrean(); continue; }
                        this.antrean.shift(); this.simpanAntrean(); this.pesan = 'Terkirim.';
                    }
                } catch (e) { /* luring: biarkan di antrean */ } finally { this.mengirim = false; }
            },
        }"
    >
        <div class="mt-3 flex items-center justify-between text-xs">
            <span x-show="!daring" class="rounded bg-aksen-latar px-2 py-1 text-peringatan">⚡ Mode luring</span>
            <span x-show="antrean.length" class="text-tinta-3"><span x-text="antrean.length"></span> isian menunggu kirim. Akan terkirim otomatis saat ada sinyal.</span>
        </div>

        <div class="mt-3 space-y-2">
            <select x-model="form.classroom_id" class="block w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm">
                <option value="">Kelas</option>
                @foreach ($kelas as $k) <option value="{{ $k->id }}">{{ $k->school?->nama }} · {{ $k->nama }}</option> @endforeach
            </select>
            <select x-model="form.meeting_id" class="block w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm">
                <option value="">Pertemuan</option>
                @foreach ($pertemuan as $p) <option value="{{ $p->id }}">Pertemuan {{ $p->urutan }} · {{ ucfirst($p->materi) }}</option> @endforeach
            </select>
            <input type="date" x-model="form.tanggal" class="block w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm">
        </div>

        <div class="mt-3 space-y-3">
            @foreach ($butir as $b)
                <fieldset class="rounded-xl border border-garis bg-white p-3">
                    <p class="text-sm font-medium leading-relaxed">{{ $b->urutan }}. {{ $b->pernyataan }}</p>
                    <div class="mt-2 grid grid-cols-4 gap-1">
                        @foreach (range(1, 4) as $s)
                            <label class="rounded-lg border px-1 py-2 text-center text-sm" :class="form.skor[{{ $b->id }}] == {{ $s }} ? 'border-aksen bg-aksen-latar font-bold' : 'border-garis-2'">
                                <input type="radio" class="sr-only" name="ob-{{ $b->id }}" value="{{ $s }}" @change="form.skor[{{ $b->id }}] = {{ $s }}">{{ $s }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
            <textarea x-model="form.catatan" rows="3" placeholder="Catatan lapangan" class="block w-full rounded-lg border border-garis px-3 py-2 text-base"></textarea>
        </div>

        <p x-show="pesan" x-text="pesan" class="mt-3 text-sm text-tinta-2"></p>
        <button type="button" @click="simpan()" class="mt-3 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Simpan</button>
    </div>

    @if ($terkirim->isNotEmpty())
        <h2 class="mt-5 text-sm font-semibold">Terkirim</h2>
        <ul class="mt-1 space-y-1 text-xs">
            @foreach ($terkirim as $o)
                <li class="rounded-lg border border-garis-2 bg-white px-3 py-1.5" wire:key="o-{{ $o->id }}">{{ $o->tanggal->format('d/m') }} · {{ $o->classroom->nama }} · Pertemuan {{ $o->meeting->urutan }}</li>
            @endforeach
        </ul>
    @endif
</div>
