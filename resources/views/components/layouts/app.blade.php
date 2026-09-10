<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($judul) ? $judul.' · ' : '' }}{{ config('app.name', 'GEULIS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('kepala')
</head>
{{-- Lebar dasar 360 px: satu kolom, tombol setinggi jempol, tanpa sidebar. --}}
<body class="min-h-dvh bg-latar font-sans text-tinta antialiased">
    <div class="mx-auto flex min-h-dvh w-full max-w-md flex-col px-4 py-6 sm:max-w-lg">
        @if (auth()->check())
            <header class="mb-6 flex items-center justify-between gap-3">
                <a href="{{ route('beranda') }}" class="text-lg font-extrabold tracking-widest text-aksen">GEULIS</a>
                <form method="POST" action="{{ route('keluar') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-garis px-3 py-2 text-sm text-tinta-2">
                        Keluar
                    </button>
                </form>
            </header>
        @endif

        <main class="flex-1">
            @if (session('pesan'))
                <p class="mb-4 rounded-lg border border-peringatan/40 bg-aksen-latar px-3 py-2 text-sm text-peringatan">{{ session('pesan') }}</p>
            @endif
            {{ $slot }}
        </main>

        <footer class="mt-8 text-center text-xs text-tinta-3">
            Penelitian Kompetitif Universitas Siliwangi 2026
        </footer>
    </div>
    @livewireScriptConfig
</body>
</html>
