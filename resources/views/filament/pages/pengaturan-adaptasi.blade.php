<x-filament-panels::page>
    @if ($this->terkunci())
        <div class="rounded-xl border-2 border-danger-500 bg-danger-50 p-4 text-danger-800 dark:bg-danger-950 dark:text-danger-200">
            <div class="text-lg font-bold">Parameter TERKUNCI — validasi ahli sedang/sudah berjalan.</div>
            <p class="mt-1 text-sm">Mengubah nilai di bawah ini membatalkan keabsahan hasil validasi terhadap produk yang diuji. Jangan ubah tanpa keputusan tertulis ketua peneliti.</p>
        </div>
    @else
        <div class="rounded-xl border-2 border-warning-500 bg-warning-50 p-4 text-warning-800 dark:bg-warning-950 dark:text-warning-200">
            <div class="text-lg font-bold">Layar ini dikunci begitu validasi ahli dimulai.</div>
            <p class="mt-1 text-sm">Seluruh nilai ikut dinilai pada lembar validasi dan dilaporkan apa adanya di artikel. Setelah dikunci (<code>GEULIS_PARAMETER_TERKUNCI=true</code>), perubahan apa pun membatalkan hasil validasi.</p>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2">Variabel .env</th>
                    <th class="px-4 py-2">Nilai berlaku</th>
                    <th class="px-4 py-2">Arti</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->parameter() as $nama => $p)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-2 font-mono">{{ $nama }}</td>
                        <td class="px-4 py-2 font-mono font-semibold">{{ $p['nilai'] }}</td>
                        <td class="px-4 py-2">{{ $p['arti'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-sm text-gray-500">
        Nilai diubah lewat berkas <code>.env</code> (atau <code>config/geulis.php</code>) dan dicatat di <code>docs/JURNAL-SESI.md</code> beserta alasannya — bukan dari layar ini — supaya setiap perubahan meninggalkan jejak di Git.
    </p>
</x-filament-panels::page>
