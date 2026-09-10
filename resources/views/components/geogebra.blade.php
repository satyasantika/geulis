@props(['konfigurasi' => [], 'id' => 'ggb-'.uniqid()])
@php
    $app = $konfigurasi['app'] ?? 'geometry';
    $terpandu = (bool) ($konfigurasi['terpandu'] ?? true);
    $perintah = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($konfigurasi['perintah'] ?? '')))));
    $alat = array_values(array_filter(array_map('trim', (array) ($konfigurasi['alat'] ?? []))));
@endphp
{{-- GeoGebra dimuat dari server sendiri (public/geogebra), bukan CDN: jaringan sekolah memblokirnya. --}}
@once
    <script src="{{ asset('geogebra/deployggb.js') }}"></script>
@endonce
<div
    id="{{ $id }}"
    class="w-full overflow-hidden rounded-xl border border-garis bg-white"
    style="aspect-ratio: 4 / 3; max-width: 100%;"
    x-data="{
        init() {
            const wadah = this.$el;
            const lebar = Math.min(wadah.clientWidth || 328, 640);
            const applet = new GGBApplet({
                appName: @js($app),
                width: lebar,
                height: Math.round(lebar * 0.75),
                showToolBar: true,
                showAlgebraInput: ! @js($terpandu),
                showMenuBar: false,
                enableShiftDragZoom: ! @js($terpandu),
                enableRightClick: ! @js($terpandu),
                showResetIcon: true,
                language: 'id',
                @if ($alat !== []) customToolBar: @js(implode(' | ', $alat)), @endif
                appletOnLoad(api) {
                    @foreach ($perintah as $p) api.evalCommand(@js($p)); @endforeach
                    wadah.dispatchEvent(new CustomEvent('geogebra-siap', { bubbles: true }));
                },
            }, true);
            applet.setHTML5Codebase('{{ asset('geogebra/HTML5/5.0/web3d/') }}', true);
            applet.inject(wadah.id);
        }
    }"
></div>
