/**
 * Motif Builder — komponen orisinal GEULIS (inti nilai HKI).
 *
 * Alpine component: kanvas SVG, blok perintah, mesin transformasi sisi klien
 * (meniru App\Services\Motif\MesinTransformasi persis), rasterisasi dan IoU
 * untuk PRATINJAU kemiripan. Skor resmi selalu dihitung ulang di server.
 */

const OPERASI = {
    TRANSLASI: { label: 'Geser', warna: '#0e7490' },
    REFLEKSI: { label: 'Cerminkan', warna: '#7c3aed' },
    ROTASI: { label: 'Putar', warna: '#b45309' },
    DILATASI: { label: 'Perbesar/kecilkan', warna: '#15803d' },
    ULANGI: { label: 'Ulangi', warna: '#7c2d12' },
};

const GARIS = ['x', 'y', 'y=x', 'y=-x'];

function blokBaru(op) {
    switch (op) {
        case 'TRANSLASI': return { op, vektor: [2, 0] };
        case 'REFLEKSI': return { op, garis: 'y' };
        case 'ROTASI': return { op, pusat: [0, 0], sudut: 90 };
        case 'DILATASI': return { op, pusat: [0, 0], k: 2 };
        case 'ULANGI': return { op, n: 4, badan: [] };
        default: return { op };
    }
}

function petakan(op, p, [x, y]) {
    switch (op) {
        case 'TRANSLASI': return [x + Number(p.vektor[0]), y + Number(p.vektor[1])];
        case 'REFLEKSI':
            if (p.garis === 'x') return [x, -y];
            if (p.garis === 'y') return [-x, y];
            if (p.garis === 'y=x') return [y, x];
            return [-y, -x];
        case 'ROTASI': {
            const r = (Number(p.sudut) * Math.PI) / 180;
            const cx = Number(p.pusat[0]), cy = Number(p.pusat[1]);
            const dx = x - cx, dy = y - cy;
            return [cx + dx * Math.cos(r) - dy * Math.sin(r), cy + dx * Math.sin(r) + dy * Math.cos(r)];
        }
        case 'DILATASI': {
            const cx = Number(p.pusat[0]), cy = Number(p.pusat[1]), k = Number(p.k);
            return [cx + k * (x - cx), cy + k * (y - cy)];
        }
        default: return [x, y];
    }
}

/** Menjalankan urutan perintah; mengembalikan daftar poligon di kanvas. */
export function jalankan(motifDasar, perintah, batas = 400) {
    const kanvas = [];
    let kini = [];
    const eksekusi = (daftar) => {
        for (const p of daftar) {
            if (p.op === 'MOTIF_DASAR') {
                kanvas.push(motifDasar);
                kini = [motifDasar];
                continue;
            }
            if (p.op === 'ULANGI') {
                let lahir = [...kini];
                for (let i = 0; i < Math.max(0, Math.min(36, Number(p.n) || 0)); i++) {
                    eksekusi(p.badan || []);
                    lahir = [...lahir, ...kini];
                }
                kini = lahir;
                continue;
            }
            const baru = kini.map((poligon) => poligon.map((t) => petakan(p.op, p, t)));
            if (kanvas.length + baru.length > batas) throw new Error('Terlalu banyak bangun; kurangi pengulangan.');
            kanvas.push(...baru);
            kini = baru;
        }
    };
    eksekusi(perintah);
    return kanvas;
}

export function hitungLangkah(perintah) {
    let n = 0;
    for (const p of perintah) {
        if (p.op === 'MOTIF_DASAR') continue;
        n++;
        if (p.op === 'ULANGI') n += hitungLangkah(p.badan || []);
    }
    return n;
}

function diDalam(px, py, poligon) {
    let dalam = false;
    for (let a = 0, b = poligon.length - 1; a < poligon.length; b = a++) {
        const [xa, ya] = poligon[a], [xb, yb] = poligon[b];
        if ((ya > py) !== (yb > py)) {
            const x = ((xb - xa) * (py - ya)) / (yb - ya) + xa;
            if (px < x) dalam = !dalam;
        }
    }
    return dalam;
}

/** Raster boolean resolusi², sama dengan Rasterizer PHP. */
export function raster(poligonList, resolusi, min, maks) {
    const sel = (maks - min) / resolusi;
    const hasil = new Uint8Array(resolusi * resolusi);
    for (const poligon of poligonList) {
        if (poligon.length < 3) continue;
        const xs = poligon.map((t) => t[0]), ys = poligon.map((t) => t[1]);
        const i0 = Math.max(0, Math.floor((Math.min(...xs) - min) / sel));
        const i1 = Math.min(resolusi - 1, Math.ceil((Math.max(...xs) - min) / sel));
        const j0 = Math.max(0, Math.floor((Math.min(...ys) - min) / sel));
        const j1 = Math.min(resolusi - 1, Math.ceil((Math.max(...ys) - min) / sel));
        for (let j = j0; j <= j1; j++) {
            const py = min + (j + 0.5) * sel;
            for (let i = i0; i <= i1; i++) {
                const idx = j * resolusi + i;
                if (hasil[idx]) continue;
                if (diDalam(min + (i + 0.5) * sel, py, poligon)) hasil[idx] = 1;
            }
        }
    }
    return hasil;
}

export function iou(a, b) {
    let irisan = 0, gabungan = 0;
    for (let i = 0; i < a.length; i++) {
        if (a[i] && b[i]) irisan++;
        if (a[i] || b[i]) gabungan++;
    }
    return gabungan === 0 ? 0 : irisan / gabungan;
}

export function motifBuilder(konfigurasi, kiriman = null) {
    const kisi = konfigurasi.kisi || { min: -6, maks: 6 };
    const resolusi = Number(konfigurasi.resolusi || 200);
    const sasaranPoligon = jalankan(konfigurasi.motif_dasar, konfigurasi.sasaran);
    const sasaranRaster = raster(sasaranPoligon, resolusi, kisi.min, kisi.maks);

    return {
        OPERASI,
        GARIS,
        kisi,
        blokTersedia: konfigurasi.blok || Object.keys(OPERASI),
        sasaranPoligon,
        /* Tahap 1: tandai motif dasar pada sasaran (bukti dekomposisi). */
        tahap: kiriman ? 'hasil' : 'penanda',
        ditandai: kiriman?.motif_dasar_ditandai || [],
        /* Tahap 2: susun blok. */
        perintah: kiriman?.urutan_perintah?.filter((p) => p.op !== 'MOTIF_DASAR') || [],
        hasilPoligon: [],
        kemiripan: null,
        galat: null,
        terpilih: null, // indeks blok yang sedang disunting (tingkat atas: "i", dalam ULANGI: "i.j")
        mengirim: false,

        init() {
            this.$watch('perintah', () => this.jalankanSekarang(), { deep: true });
            if (this.perintah.length) this.jalankanSekarang();
        },

        // ---------- tampilan ----------
        titikKeSvg([x, y]) {
            const lebar = this.kisi.maks - this.kisi.min;
            return [((x - this.kisi.min) / lebar) * 100, ((this.kisi.maks - y) / lebar) * 100];
        },
        poligonKePath(poligon) {
            return poligon.map((t, i) => (i ? 'L' : 'M') + this.titikKeSvg(t).map((v) => v.toFixed(2)).join(' ')).join(' ') + ' Z';
        },
        garisKisi() {
            const g = [];
            for (let v = Math.ceil(this.kisi.min); v <= Math.floor(this.kisi.maks); v++) {
                const p = this.titikKeSvg([v, v]);
                g.push({ x: p[0], y: p[1], sumbu: v === 0 });
            }
            return g;
        },

        // ---------- penanda motif ----------
        toggleTanda(i) {
            if (this.tahap !== 'penanda') return;
            const pos = this.ditandai.indexOf(i);
            pos >= 0 ? this.ditandai.splice(pos, 1) : this.ditandai.push(i);
        },
        selesaiMenandai() {
            if (!this.ditandai.length) { this.galat = 'Tandai dulu bagian yang menurutmu motif dasarnya (satuan terkecil yang berulang).'; return; }
            this.galat = null;
            this.tahap = 'susun';
        },

        // ---------- blok ----------
        tambah(op, keDalam = null) {
            const b = blokBaru(op);
            if (keDalam !== null) {
                if (op === 'ULANGI') return; // satu tingkat perulangan cukup untuk HP
                this.perintah[keDalam].badan.push(b);
                this.terpilih = `${keDalam}.${this.perintah[keDalam].badan.length - 1}`;
            } else {
                this.perintah.push(b);
                this.terpilih = String(this.perintah.length - 1);
            }
        },
        hapus(path) {
            const [i, j] = path.split('.').map(Number);
            if (Number.isNaN(j)) this.perintah.splice(i, 1);
            else this.perintah[i].badan.splice(j, 1);
            this.terpilih = null;
        },
        geser(path, arah) {
            const [i, j] = path.split('.').map(Number);
            const daftar = Number.isNaN(j) ? this.perintah : this.perintah[i].badan;
            const idx = Number.isNaN(j) ? i : j;
            const tujuan = idx + arah;
            if (tujuan < 0 || tujuan >= daftar.length) return;
            [daftar[idx], daftar[tujuan]] = [daftar[tujuan], daftar[idx]];
            this.terpilih = Number.isNaN(j) ? String(tujuan) : `${i}.${tujuan}`;
        },
        blok(path) {
            const [i, j] = path.split('.').map(Number);
            return Number.isNaN(j) ? this.perintah[i] : this.perintah[i].badan[j];
        },
        ringkas(b) {
            switch (b.op) {
                case 'TRANSLASI': return `Geser (${b.vektor[0]}, ${b.vektor[1]})`;
                case 'REFLEKSI': return `Cerminkan pada ${b.garis === 'x' ? 'sumbu-x' : b.garis === 'y' ? 'sumbu-y' : 'garis ' + b.garis}`;
                case 'ROTASI': return `Putar ${b.sudut}° pusat (${b.pusat[0]}, ${b.pusat[1]})`;
                case 'DILATASI': return `Skala k=${b.k} pusat (${b.pusat[0]}, ${b.pusat[1]})`;
                case 'ULANGI': return `Ulangi ${b.n} kali`;
                default: return b.op;
            }
        },
        langkah() { return hitungLangkah(this.perintah); },

        // ---------- jalankan & pratinjau ----------
        urutanLengkap() { return [{ op: 'MOTIF_DASAR' }, ...JSON.parse(JSON.stringify(this.perintah))]; },
        jalankanSekarang() {
            try {
                this.hasilPoligon = jalankan(konfigurasi.motif_dasar, this.urutanLengkap());
                this.kemiripan = Math.round(iou(raster(this.hasilPoligon, resolusi, kisi.min, kisi.maks), sasaranRaster) * 1000) / 10;
                this.galat = null;
            } catch (e) {
                this.galat = e.message;
                this.kemiripan = null;
            }
        },
        cuplikanSvg() {
            const path = this.hasilPoligon.map((p) => `<path d="${this.poligonKePath(p)}" fill="#7c2d12" fill-opacity=".7"/>`).join('');
            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">${path}</svg>`;
        },
        async kirim() {
            if (this.mengirim) return;
            if (!this.perintah.length) { this.galat = 'Susun setidaknya satu perintah dulu.'; return; }
            this.mengirim = true;
            try {
                await this.$wire.kirim(this.urutanLengkap(), this.ditandai, this.cuplikanSvg());
                this.tahap = 'hasil';
            } finally {
                this.mengirim = false;
            }
        },
        cobaLagi() { this.tahap = 'susun'; },
    };
}
