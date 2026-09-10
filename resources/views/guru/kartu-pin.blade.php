<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu PIN {{ $kelas->nama }}</title>
    {{-- Sengaja tanpa Vite: halaman cetak harus mandiri dan ringan. --}}
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; color: #000; margin: 0; padding: 12px; }
        .alat { display: flex; gap: 8px; align-items: center; margin-bottom: 12px; font-size: 14px; }
        .alat button { padding: 8px 14px; font-size: 14px; }
        .kisi { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; }
        .kartu { border: 1px dashed #000; padding: 8px 10px; height: 44mm; break-inside: avoid; display: flex; flex-direction: column; justify-content: space-between; }
        .judul { font-size: 10px; letter-spacing: .12em; font-weight: 800; }
        .kelas { font-size: 9px; }
        .nama { font-size: 12px; font-weight: 700; margin-top: 4px; }
        .nis { font-size: 10px; }
        .pin { font-size: 20px; font-weight: 800; letter-spacing: .3em; font-family: ui-monospace, monospace; }
        .alamat { font-size: 8px; }
        @media print { .alat { display: none; } body { padding: 0; } }
        @media (max-width: 600px) { .kisi { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>
    <div class="alat">
        <button type="button" onclick="window.print()">Cetak</button>
        <span>{{ $kelas->nama }} · {{ $siswa->count() }} kartu · gunting mengikuti garis putus-putus</span>
    </div>
    <div class="kisi">
        @foreach ($siswa as $s)
            <div class="kartu">
                <div>
                    <div class="judul">GEULIS</div>
                    <div class="kelas">{{ $kelas->school?->nama }} · {{ $kelas->nama }}</div>
                    <div class="nama">{{ $s->nama }}</div>
                    <div class="nis">NIS {{ $s->username }}</div>
                </div>
                <div>
                    <div class="pin">{{ $s->pin_kartu ?? '— — —' }}</div>
                    <div class="alamat">{{ $alamat }} · simpan kartu ini, jangan bagikan PIN</div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
