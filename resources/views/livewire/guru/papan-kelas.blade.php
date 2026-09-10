<div>
    <a href="{{ route('guru.kelas', $kelas) }}" wire:navigate class="text-sm text-tinta-3">&larr; {{ $kelas->nama }}</a>
    <h1 class="mt-2 text-xl font-bold">{{ $kelas->nama }} <span class="text-sm font-normal text-tinta-3">· {{ $kelas->school?->nama }}</span></h1>
    <p class="text-sm text-tinta-2">{{ $siswa->count() }} siswa · kelompok {{ str_replace('_', ' ', $kelas->kelompok_riset) }} · <span class="text-ok">{{ $aktif }} aktif</span> · {{ $belum_mulai->count() }} belum mulai</p>

    <section class="mt-4 rounded-xl border border-peringatan/40 bg-aksen-latar p-3">
        <h2 class="text-sm font-semibold text-peringatan">Perlu pendampingan langsung — {{ $pendampingan->count() + $belum_mulai->count() }} siswa</h2>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($pendampingan as $p)
                <li wire:key="pd-{{ $p->id }}"><a href="{{ route('guru.siswa', $p->user) }}" wire:navigate class="font-medium">{{ $p->user->kode_anonim }} · {{ $p->user->nama }}</a> <span class="text-tinta-2">· P{{ $p->lessonUnit->meeting->urutan }} {{ $p->lessonUnit->meeting->materi }} · {{ $p->iterasi_remedial }}× remedial</span></li>
            @endforeach
            @foreach ($belum_mulai as $s)
                <li wire:key="bm-{{ $s->id }}"><a href="{{ route('guru.siswa', $s) }}" wire:navigate class="font-medium">{{ $s->kode_anonim }} · {{ $s->nama }}</a> <span class="text-tinta-2">· belum mulai asesmen awal</span></li>
            @endforeach
            @if ($pendampingan->isEmpty() && $belum_mulai->isEmpty())
                <li class="text-tinta-2">Tidak ada. Semua berjalan.</li>
            @endif
        </ul>
        @foreach ($pola as $p)
            <p class="mt-2 text-xs text-peringatan"><b>Pola yang terlihat:</b> {{ $p['jumlah'] }} dari {{ $siswa->count() }} siswa tersendat di unit yang sama — P{{ $p['unit']->meeting->urutan }} {{ $p['unit']->meeting->judul }}. Ini bukan masalah perorangan; pertimbangkan membahasnya klasikal.</p>
        @endforeach
    </section>

    <h2 class="mt-5 text-sm font-semibold">Peta panas penguasaan (M) per pemeriksaan</h2>
    <div class="mt-2 overflow-x-auto rounded-xl border border-garis bg-white">
        <table class="w-full text-xs">
            <thead class="bg-latar text-left">
                <tr>
                    <th class="sticky left-0 bg-latar px-2 py-2">Siswa</th>
                    @foreach ($unit as $u) <th class="px-2 py-2 text-center">P{{ $u->meeting->urutan }}</th> @endforeach
                    <th class="px-2 py-2">Level</th>
                    <th class="px-2 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($siswa as $s)
                    <tr class="border-t border-garis-2" wire:key="baris-{{ $s->id }}">
                        <td class="sticky left-0 bg-white px-2 py-1.5 whitespace-nowrap"><a href="{{ route('guru.siswa', $s) }}" wire:navigate><b>{{ $s->kode_anonim }}</b> {{ \Illuminate\Support\Str::limit($s->nama, 14) }}</a></td>
                        @foreach ($unit as $u)
                            @php $nilai = $m[$s->id][$u->id] ?? null; @endphp
                            <td class="px-1 py-1 text-center"><span class="inline-block w-10 rounded py-1 font-mono {{ \App\Services\Guru\PapanKelasService::warna($nilai) }}">{{ $nilai === null ? '–' : number_format($nilai, 2, ',') }}</span></td>
                        @endforeach
                        <td class="px-2 py-1.5 font-semibold">{{ $level[$s->id] ?? '–' }}</td>
                        <td class="px-2 py-1.5"><button type="button" wire:click="bukaUbah({{ $s->id }}, '{{ $level[$s->id] ?? 'L2' }}')" class="rounded border border-garis px-2 py-1 text-tinta-2">Ubah</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-2 flex flex-wrap gap-2 text-[10px] text-tinta-3">
        <span><span class="inline-block h-2.5 w-2.5 rounded bg-green-200"></span> M ≥ 0,80</span>
        <span><span class="inline-block h-2.5 w-2.5 rounded bg-lime-100"></span> 0,65–0,79</span>
        <span><span class="inline-block h-2.5 w-2.5 rounded bg-amber-100"></span> 0,50–0,64</span>
        <span><span class="inline-block h-2.5 w-2.5 rounded bg-red-100"></span> &lt; 0,50</span>
        <span><span class="inline-block h-2.5 w-2.5 rounded bg-garis-2"></span> belum dikerjakan</span>
    </p>

    @if ($ubahSiswaId)
        @php $target = $siswa->firstWhere('id', $ubahSiswaId); @endphp
        <div class="fixed inset-0 z-20 flex items-end bg-black/40 sm:items-center sm:justify-center">
            <form wire:submit="simpanUbah" class="w-full max-w-md rounded-t-2xl bg-white p-4 sm:rounded-2xl">
                <h3 class="font-semibold">Ubah level {{ $target?->nama }}</h3>
                <p class="mt-1 text-xs text-tinta-2">Anda boleh mengesampingkan keputusan sistem kapan saja. Alasannya wajib — ini data penelitian yang berharga.</p>
                <div class="mt-3 grid grid-cols-3 gap-1">
                    @foreach ($labelLevel as $kode => $nama)
                        <label class="rounded-lg border px-2 py-2 text-center text-sm has-[:checked]:border-aksen has-[:checked]:bg-aksen-latar has-[:checked]:font-bold">
                            <input type="radio" class="sr-only" wire:model="levelBaru" value="{{ $kode }}">{{ $kode }}<br><span class="text-[10px] font-normal">{{ $nama }}</span>
                        </label>
                    @endforeach
                </div>
                <textarea wire:model="alasan" rows="3" placeholder="Alasan (wajib): apa yang Anda lihat di kelas yang tidak terlihat sistem?" class="mt-3 block w-full rounded-lg border border-garis px-3 py-2 text-base"></textarea>
                @error('alasan') <p class="mt-1 text-xs text-peringatan">{{ $message }}</p> @enderror
                <div class="mt-3 flex gap-2">
                    <button type="submit" class="flex-1 rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Simpan override</button>
                    <button type="button" wire:click="batalUbah" class="rounded-lg border border-garis px-4 py-3 text-tinta-2">Batal</button>
                </div>
            </form>
        </div>
    @endif
</div>
