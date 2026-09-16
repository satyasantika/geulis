<div class="flex min-h-dvh flex-col items-center justify-center px-4 py-8" wire:poll.2s>
    <p class="text-sm font-semibold tracking-widest text-aksen">GEULIS · simulasi</p>

    @if ($token === null)
        <h1 class="mt-4 text-center text-2xl font-bold">Semua peserta sudah masuk</h1>
        <p class="mt-2 max-w-md text-center text-sm text-tinta-2">{{ $sudah }} / {{ $total }} akun. Layar ini boleh ditutup. Hapus sesi dari panel admin setelah selesai.</p>
    @else
        <p class="mt-2 text-sm text-tinta-3">Antrian {{ $token->urutan }} dari {{ $total }} · {{ $sudah }} sudah masuk</p>
        <h1 class="mt-3 text-center text-2xl font-bold">{{ $token->user->nama }}</h1>
        <p class="mt-1 text-center text-sm text-tinta-2">{{ $token->user->punyaPeran(\App\Enums\Peran::Guru) ? 'Guru' : 'Siswa' }} · pindai, lalu ketuk Masuk di HP</p>
        <div class="mt-6 w-full max-w-xs rounded-2xl border border-garis bg-white p-4 [&>svg]:h-auto [&>svg]:w-full">
            {!! $qrSvg !!}
        </div>
    @endif
</div>
