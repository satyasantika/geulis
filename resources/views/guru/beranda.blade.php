<x-layouts.app judul="Beranda Guru">
    <h1 class="text-xl font-bold">Selamat datang, {{ auth()->user()->nama }}</h1>
    <p class="mt-2 text-sm text-tinta-2">
        Daftar kelas dan kartu PIN akan tersedia di sini.
    </p>
    {{-- G-01 dan G-05 dikerjakan pada butir berikutnya Sprint 0. --}}
</x-layouts.app>
