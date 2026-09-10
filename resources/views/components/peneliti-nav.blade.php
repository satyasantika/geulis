@props(['aktif' => ''])
<nav class="flex flex-wrap gap-1 text-xs">
    @foreach (['kelengkapan' => ['P-01 Kelengkapan', route('riset.kelengkapan')], 'analitik' => ['P-02 Analitik', route('riset.analitik')], 'validasi' => ['V-02 Aiken', route('riset.validasi')], 'ekspor' => ['P-03 Ekspor', route('riset.ekspor')]] as $kode => [$label, $url])
        <a href="{{ $url }}" wire:navigate class="rounded-full px-3 py-1.5 {{ $aktif === $kode ? 'bg-aksen text-white' : 'border border-garis bg-white text-tinta-2' }}">{{ $label }}</a>
    @endforeach
    <a href="/admin" class="rounded-full border border-garis bg-white px-3 py-1.5 text-tinta-2">Panel admin</a>
</nav>
