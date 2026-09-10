<x-layouts.app judul="Jalur Belajarmu">
    <h1 class="text-xl font-bold">Halo, {{ auth()->user()->nama }}</h1>
    <p class="mt-2 text-sm text-tinta-2">
        Jalur belajarmu akan muncul di sini setelah asesmen awal dibuka oleh gurumu.
    </p>
    {{-- S-04 dibangun pada Sprint 2; kerangka ini hanya menutup alur masuk. --}}
</x-layouts.app>
