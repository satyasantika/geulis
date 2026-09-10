<x-layouts.app judul="Persetujuan Penelitian">
    <h1 class="text-xl font-bold">Persetujuan Penelitian</h1>
    <p class="mt-2 text-sm text-tinta-2">Kegiatan belajarmu direkam untuk penelitian Universitas Siliwangi.</p>

    <div class="mt-4 space-y-3 rounded-xl border border-garis bg-aksen-latar p-4 text-sm leading-relaxed">
        <p><b>Yang dikumpulkan:</b> jawaban latihan &amp; tes, waktu pengerjaan, karya yang kamu buat.</p>
        <p><b>Yang TIDAK dikumpulkan:</b> NIK, alamat rumah, nomor HP, foto wajah.</p>
        <p><b>Namamu diganti kode</b> ({{ auth()->user()->kode_anonim ?? 'S-000' }}) pada semua laporan dan artikel.</p>
    </div>

    <form method="POST" action="{{ route('siswa.persetujuan.simpan') }}" class="mt-5 space-y-3">
        @csrf
        <label class="flex items-start gap-3 rounded-xl border border-garis bg-white p-4">
            <input type="radio" name="setuju" value="ya" class="mt-1" @checked(old('setuju', $consent?->setuju_data_penelitian ? 'ya' : null) === 'ya')>
            <span class="text-sm">Saya bersedia data belajar saya dipakai untuk penelitian.</span>
        </label>
        <label class="flex items-start gap-3 rounded-xl border border-garis bg-white p-4">
            <input type="radio" name="setuju" value="tidak" class="mt-1" @checked(old('setuju', $consent && ! $consent->setuju_data_penelitian ? 'tidak' : null) === 'tidak')>
            <span class="text-sm">Saya tidak bersedia — saya tetap ingin belajar di sini.</span>
        </label>
        @error('setuju') <p class="text-sm text-peringatan">{{ $message }}</p> @enderror
        <button type="submit" class="block w-full rounded-lg bg-aksen px-4 py-3 font-semibold text-white">Lanjut</button>
    </form>

    <p class="mt-4 text-xs text-tinta-3">Kamu bisa mengubah pilihan ini kapan saja lewat menu Persetujuan.</p>
</x-layouts.app>
