<x-layouts.app judul="Masuk">
    <div class="my-6 text-center">
        <div class="text-3xl font-extrabold tracking-widest text-aksen">GEULIS</div>
        <p class="mt-2 text-sm text-tinta-3">Belajar Transformasi Geometri<br>lewat Budaya Priangan Timur</p>
    </div>

    <form method="POST" action="{{ route('masuk.proses') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label for="username" class="mb-1 block text-sm font-medium text-tinta-2">NIS</label>
            <input
                id="username"
                name="username"
                type="text"
                inputmode="numeric"
                autocomplete="username"
                autofocus
                required
                value="{{ old('username') }}"
                class="block w-full rounded-lg border border-garis bg-white px-3 py-3 text-base focus:border-aksen focus:outline-none focus:ring-2 focus:ring-aksen/30"
            >
        </div>

        <div>
            <label for="pin" class="mb-1 block text-sm font-medium text-tinta-2">PIN (6 digit)</label>
            <input
                id="pin"
                name="pin"
                type="password"
                inputmode="numeric"
                autocomplete="current-password"
                required
                class="block w-full rounded-lg border border-garis bg-white px-3 py-3 text-base tracking-[0.4em] focus:border-aksen focus:outline-none focus:ring-2 focus:ring-aksen/30"
            >
        </div>

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-peringatan/40 bg-aksen-latar px-3 py-2 text-sm text-peringatan">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="block w-full rounded-lg bg-aksen px-4 py-3 text-base font-semibold text-white active:bg-aksen-2">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-tinta-3">Lupa PIN? Minta gurumu mengatur ulang.</p>
</x-layouts.app>
