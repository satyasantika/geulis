<x-layouts.simulasi judul="Masuk simulasi">
    <div class="mx-auto flex min-h-dvh w-full max-w-md flex-col justify-center px-4 py-8">
        <p class="text-center text-sm font-semibold tracking-widest text-aksen">GEULIS · simulasi</p>
        <h1 class="mt-4 text-center text-2xl font-bold">Masuk sebagai {{ $token->user->nama }}</h1>
        <p class="mt-2 text-center text-sm text-tinta-2">{{ $token->user->punyaPeran(\App\Enums\Peran::Guru) ? 'Guru' : 'Siswa' }} · tanpa sandi, sekali pakai</p>
        <form method="POST" action="{{ route('simulasi.masuk.proses', $token->token) }}" class="mt-6">
            @csrf
            <button type="submit" class="block w-full rounded-lg bg-aksen px-4 py-3 text-base font-semibold text-white">Masuk</button>
        </form>
    </div>
</x-layouts.simulasi>
