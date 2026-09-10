<div>
    <a href="{{ route('siswa.pertemuan', $meeting) }}" wire:navigate class="text-sm text-tinta-3">&larr; Pertemuan {{ $meeting->urutan }}</a>
    <div class="mt-2 text-xs text-tinta-3">Motif Builder · {{ ucfirst($meeting->materi) }}</div>
    <h1 class="text-lg font-bold leading-snug">{{ $aktivitas->judul }}</h1>

    <div
        wire:ignore
        x-data="motifBuilder(@js($konfigurasi), @js($hasil))"
        class="mt-4"
    >
        {{-- Dua kanvas: sasaran & hasil. Pada 360 px keduanya 2 kolom ±160 px. --}}
        <div class="grid grid-cols-2 gap-2">
            <div>
                <div class="mb-1 flex items-center justify-between text-xs text-tinta-3">
                    <span>Sasaran</span>
                    <span x-show="tahap === 'penanda'" class="text-aksen">ketuk motif dasar</span>
                </div>
                <svg viewBox="0 0 100 100" class="aspect-square w-full rounded-xl border border-garis bg-white touch-manipulation" role="img" aria-label="Motif sasaran">
                    <template x-for="g in garisKisi()" :key="'gs'+g.x">
                        <g><line :x1="g.x" y1="0" :x2="g.x" y2="100" :stroke="g.sumbu ? '#a8a29e' : '#e7e5e4'" stroke-width=".4"/>
                           <line x1="0" :y1="g.y" x2="100" :y2="g.y" :stroke="g.sumbu ? '#a8a29e' : '#e7e5e4'" stroke-width=".4"/></g>
                    </template>
                    <template x-for="(p, i) in sasaranPoligon" :key="'s'+i">
                        <path :d="poligonKePath(p)" @click="toggleTanda(i)"
                              :fill="ditandai.includes(i) ? '#c2410c' : '#57534e'" :fill-opacity="ditandai.includes(i) ? .95 : .55"
                              :stroke="ditandai.includes(i) ? '#7c2d12' : 'none'" stroke-width="1" style="cursor:pointer"/>
                    </template>
                </svg>
            </div>
            <div>
                <div class="mb-1 flex items-center justify-between text-xs text-tinta-3">
                    <span>Hasilmu</span>
                    <span x-show="kemiripan !== null" class="font-semibold" :class="kemiripan >= {{ $ambang }} ? 'text-ok' : 'text-aksen'" x-text="'≈ ' + kemiripan + '%'"></span>
                </div>
                <svg viewBox="0 0 100 100" class="aspect-square w-full rounded-xl border border-garis bg-white" role="img" aria-label="Hasil susunanmu">
                    <template x-for="g in garisKisi()" :key="'gh'+g.x">
                        <g><line :x1="g.x" y1="0" :x2="g.x" y2="100" :stroke="g.sumbu ? '#a8a29e' : '#e7e5e4'" stroke-width=".4"/>
                           <line x1="0" :y1="g.y" x2="100" :y2="g.y" :stroke="g.sumbu ? '#a8a29e' : '#e7e5e4'" stroke-width=".4"/></g>
                    </template>
                    <template x-for="(p, i) in hasilPoligon" :key="'h'+i">
                        <path :d="poligonKePath(p)" fill="#7c2d12" fill-opacity=".7"/>
                    </template>
                </svg>
            </div>
        </div>

        <p x-show="galat" x-text="galat" class="mt-3 rounded-lg border border-peringatan/40 bg-aksen-latar px-3 py-2 text-sm text-peringatan"></p>

        {{-- Tahap 1: Penanda Motif --}}
        <section x-show="tahap === 'penanda'" class="mt-4 rounded-xl border border-garis bg-white p-3">
            <p class="text-sm font-semibold">Langkah 1 · Tandai motif dasarnya</p>
            <p class="mt-1 text-xs text-tinta-2">Ketuk bagian pada <b>Sasaran</b> yang menurutmu satuan terkecil yang berulang. Ini caramu memecah motif sebelum membangunnya.</p>
            <p class="mt-2 text-xs text-tinta-3"><span x-text="ditandai.length"></span> bagian ditandai</p>
            <button type="button" @click="selesaiMenandai()" class="mt-3 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Lanjut menyusun</button>
        </section>

        {{-- Tahap 2: Susun blok --}}
        <section x-show="tahap === 'susun'" class="mt-4">
            <p class="text-sm font-semibold">Langkah 2 · Susun perintahnya</p>
            <p class="mt-1 text-xs text-tinta-2">Mulai dari <b>MOTIF DASAR</b>, lalu tambahkan perintah. Setiap perintah bekerja pada hasil terakhir.</p>

            <ol class="mt-3 space-y-1.5">
                <li class="rounded-lg border border-garis bg-latar px-3 py-2 text-sm font-semibold text-tinta-2">1. MOTIF DASAR</li>
                <template x-for="(b, i) in perintah" :key="'b'+i">
                    <li>
                        <div class="rounded-lg border bg-white px-3 py-2 text-sm" :class="terpilih === String(i) ? 'border-aksen' : 'border-garis'" @click="terpilih = String(i)">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" :style="'background:' + OPERASI[b.op].warna"></span>
                                <span class="flex-1 font-medium" x-text="(i + 2) + '. ' + ringkas(b)"></span>
                                <button type="button" @click.stop="geser(String(i), -1)" class="px-1.5 text-tinta-3" aria-label="naik">▲</button>
                                <button type="button" @click.stop="geser(String(i), 1)" class="px-1.5 text-tinta-3" aria-label="turun">▼</button>
                                <button type="button" @click.stop="hapus(String(i))" class="px-1.5 text-peringatan" aria-label="hapus">✕</button>
                            </div>
                            <template x-if="b.op === 'ULANGI'">
                                <ol class="mt-2 space-y-1 border-l-2 border-aksen/40 pl-2">
                                    <template x-for="(c, j) in b.badan" :key="'c'+i+'-'+j">
                                        <li class="rounded-md border px-2 py-1.5 text-xs" :class="terpilih === i+'.'+j ? 'border-aksen' : 'border-garis-2'" @click.stop="terpilih = i+'.'+j">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2 w-2 shrink-0 rounded-full" :style="'background:' + OPERASI[c.op].warna"></span>
                                                <span class="flex-1" x-text="ringkas(c)"></span>
                                                <button type="button" @click.stop="geser(i+'.'+j, -1)" class="px-1 text-tinta-3">▲</button>
                                                <button type="button" @click.stop="geser(i+'.'+j, 1)" class="px-1 text-tinta-3">▼</button>
                                                <button type="button" @click.stop="hapus(i+'.'+j)" class="px-1 text-peringatan">✕</button>
                                            </div>
                                        </li>
                                    </template>
                                    <li class="flex flex-wrap gap-1 pt-1">
                                        <template x-for="op in blokTersedia.filter(o => o !== 'ULANGI')" :key="'in'+i+op">
                                            <button type="button" @click.stop="tambah(op, i)" class="rounded-md border border-garis-2 px-2 py-1 text-[11px]" x-text="'+ ' + OPERASI[op].label"></button>
                                        </template>
                                    </li>
                                </ol>
                            </template>
                        </div>
                    </li>
                </template>
            </ol>

            {{-- Palet blok --}}
            <div class="mt-3 flex flex-wrap gap-1.5">
                <template x-for="op in blokTersedia" :key="'p'+op">
                    <button type="button" @click="tambah(op)" class="rounded-lg px-3 py-2 text-xs font-semibold text-white" :style="'background:' + OPERASI[op].warna" x-text="'+ ' + OPERASI[op].label"></button>
                </template>
            </div>

            {{-- Penyunting parameter blok terpilih --}}
            <template x-if="terpilih !== null && blok(terpilih)">
                <div class="mt-3 rounded-xl border border-aksen bg-aksen-latar p-3 text-sm">
                    <p class="text-xs font-semibold text-aksen" x-text="OPERASI[blok(terpilih).op].label"></p>
                    <template x-if="blok(terpilih).op === 'TRANSLASI'">
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <label class="text-xs">Geser x <input type="number" step="0.5" inputmode="decimal" x-model.number="blok(terpilih).vektor[0]" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                            <label class="text-xs">Geser y <input type="number" step="0.5" inputmode="decimal" x-model.number="blok(terpilih).vektor[1]" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                        </div>
                    </template>
                    <template x-if="blok(terpilih).op === 'REFLEKSI'">
                        <div class="mt-2 grid grid-cols-4 gap-1">
                            <template x-for="g in GARIS" :key="g">
                                <button type="button" @click="blok(terpilih).garis = g" class="rounded-lg border px-1 py-2 text-xs" :class="blok(terpilih).garis === g ? 'border-aksen bg-white font-bold' : 'border-garis-2'" x-text="g === 'x' ? 'sumbu-x' : g === 'y' ? 'sumbu-y' : g"></button>
                            </template>
                        </div>
                    </template>
                    <template x-if="blok(terpilih).op === 'ROTASI'">
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <label class="text-xs">Sudut ° <input type="number" step="5" inputmode="decimal" x-model.number="blok(terpilih).sudut" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                            <label class="text-xs">Pusat x <input type="number" step="0.5" inputmode="decimal" x-model.number="blok(terpilih).pusat[0]" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                            <label class="text-xs">Pusat y <input type="number" step="0.5" inputmode="decimal" x-model.number="blok(terpilih).pusat[1]" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                        </div>
                    </template>
                    <template x-if="blok(terpilih).op === 'DILATASI'">
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <label class="text-xs">Faktor k <input type="number" step="0.5" inputmode="decimal" x-model.number="blok(terpilih).k" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                            <label class="text-xs">Pusat x <input type="number" step="0.5" inputmode="decimal" x-model.number="blok(terpilih).pusat[0]" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                            <label class="text-xs">Pusat y <input type="number" step="0.5" inputmode="decimal" x-model.number="blok(terpilih).pusat[1]" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                        </div>
                    </template>
                    <template x-if="blok(terpilih).op === 'ULANGI'">
                        <label class="mt-2 block text-xs">Berapa kali? <input type="number" min="1" max="36" inputmode="numeric" x-model.number="blok(terpilih).n" class="mt-1 block w-full rounded-lg border border-garis px-2 py-2 text-base"></label>
                    </template>
                </div>
            </template>

            <div class="mt-4 flex items-center justify-between text-xs text-tinta-3">
                <span><span x-text="langkah()"></span> langkah</span>
                <span x-show="kemiripan !== null">pratinjau kemiripan <b x-text="kemiripan + '%'"></b> · lolos ≥ {{ (int) $ambang }}</span>
            </div>
            <button type="button" @click="kirim()" :disabled="mengirim" class="mt-2 block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white disabled:opacity-60">
                <span x-show="!mengirim">Kirim dan nilai</span><span x-show="mengirim">Menilai…</span>
            </button>
        </section>

        {{-- Tahap 3: hasil --}}
        <section x-show="tahap === 'hasil'" class="mt-4">
            @if ($hasil)
                <div class="rounded-xl border p-4 {{ $hasil['lolos'] ? 'border-ok/50 bg-green-50' : 'border-garis bg-white' }}">
                    <div class="text-2xl font-bold {{ $hasil['lolos'] ? 'text-ok' : 'text-aksen' }}">
                        Kemiripan {{ number_format($hasil['kemiripan'], 0) }}% {{ $hasil['lolos'] ? '· Lolos' : '' }}
                    </div>
                    <p class="mt-1 text-sm text-tinta-2">
                        @if ($hasil['lolos'])
                            Algoritmamu menghasilkan motif sasaran. Efisiensi {{ number_format($hasil['efisiensi'], 0) }}% ({{ $hasil['langkah_siswa'] }} langkah, minimum {{ $hasil['langkah_minimum'] }}) — algoritma yang benar tetap lolos meski lebih panjang; ringkas adalah bonus.
                        @else
                            Belum sampai {{ (int) $ambang }}%. Bandingkan bentuk hasilmu dengan sasaran: bagian mana yang belum muncul, atau muncul di tempat yang salah?
                        @endif
                    </p>
                    <p class="mt-2 text-xs text-tinta-3">Percobaan ke-{{ $hasil['percobaan_ke'] }} · {{ count($hasil['motif_dasar_ditandai'] ?? []) }} motif dasar ditandai</p>
                </div>
            @endif

            <div class="mt-3 flex flex-col gap-2">
                @if (! $selesai)
                    <button type="button" @click="cobaLagi()" class="block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Ubah susunan dan coba lagi</button>
                    @if ($bolehLanjut)
                        <button type="button" wire:click="lanjutkan" class="block w-full rounded-lg border border-garis px-4 py-3 text-tinta-2">Lanjutkan ke bagian berikutnya ({{ $jumlahPercobaan }} percobaan tersimpan)</button>
                    @endif
                @else
                    <a href="{{ route('siswa.pertemuan', $meeting) }}" wire:navigate class="block w-full rounded-lg bg-aksen px-4 py-3 text-center font-semibold text-white">Kembali ke pertemuan</a>
                    <button type="button" @click="cobaLagi()" class="block w-full rounded-lg border border-garis px-4 py-3 text-tinta-2">Coba susunan yang lebih ringkas</button>
                @endif
            </div>
        </section>
    </div>
</div>
