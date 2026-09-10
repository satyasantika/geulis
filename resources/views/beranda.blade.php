<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="GEULIS: prototipe LMS Penelitian Kompetitif Universitas Siliwangi 2026, skema PPKap. Desain pembelajaran digital adaptif berbasis etnomatematika Priangan Timur untuk computational thinking pada transformasi geometri.">
    <title>GEULIS · Penelitian Kompetitif Universitas Siliwangi 2026</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-dvh bg-latar font-sans text-tinta antialiased">
    <a href="#isi" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-10 focus:rounded-lg focus:bg-aksen focus:px-3 focus:py-2 focus:text-white">Langsung ke isi</a>

    <div class="mx-auto flex min-h-dvh w-full max-w-xl flex-col px-5 py-6 sm:max-w-2xl sm:px-8">
        <header class="flex items-start justify-between gap-4">
            <div>
                <p class="text-lg font-extrabold tracking-widest text-aksen">GEULIS</p>
                <p class="mt-1 max-w-[28ch] text-xs leading-relaxed text-tinta-3">Penelitian Kompetitif Universitas Siliwangi 2026, skema PPKap</p>
            </div>
            <a href="{{ route('masuk') }}" class="shrink-0 rounded-lg border border-garis px-3 py-2 text-sm text-tinta-2 focus:outline-none focus:ring-2 focus:ring-aksen/40">Masuk</a>
        </header>

        <main id="isi" class="flex-1">
            <section class="mt-10 sm:mt-14" aria-labelledby="judul-beranda">
                <figure class="mx-auto w-[min(100%,17.5rem)] sm:w-[19rem]">
                    <svg viewBox="0 0 240 240" class="h-auto w-full" role="img" aria-labelledby="judul-rozet">
                        <title id="judul-rozet">Rozet payung geulis: delapan kelopak, sudut pusat 45 derajat</title>
                        <circle cx="120" cy="120" r="118" fill="#fff7ed"/>
                        <g class="beranda-rozet motion-safe:animate-putar-kelopak">
                            <g fill="#7c2d12">
                                <path d="M120 106 L144 70 L132 26 L120 8 L108 26 L96 70 Z" transform="rotate(0 120 120)"/>
                                <path d="M120 106 L144 70 L132 26 L120 8 L108 26 L96 70 Z" transform="rotate(90 120 120)"/>
                                <path d="M120 106 L144 70 L132 26 L120 8 L108 26 L96 70 Z" transform="rotate(180 120 120)"/>
                                <path d="M120 106 L144 70 L132 26 L120 8 L108 26 L96 70 Z" transform="rotate(270 120 120)"/>
                            </g>
                            <g fill="#c2410c">
                                <path d="M120 106 L144 70 L132 26 L120 8 L108 26 L96 70 Z" transform="rotate(45 120 120)"/>
                                <path d="M120 106 L144 70 L132 26 L120 8 L108 26 L96 70 Z" transform="rotate(135 120 120)"/>
                                <path d="M120 106 L144 70 L132 26 L120 8 L108 26 L96 70 Z" transform="rotate(225 120 120)"/>
                                <path d="M120 106 L144 70 L132 26 L120 8 L108 26 L96 70 Z" transform="rotate(315 120 120)"/>
                            </g>
                            <g stroke="#1c1917" stroke-width="1.1" stroke-opacity=".28" fill="none">
                                <line x1="120" y1="120" x2="120" y2="10"/>
                                <line x1="120" y1="120" x2="120" y2="10" transform="rotate(45 120 120)"/>
                                <line x1="120" y1="120" x2="120" y2="10" transform="rotate(90 120 120)"/>
                                <line x1="120" y1="120" x2="120" y2="10" transform="rotate(135 120 120)"/>
                                <line x1="120" y1="120" x2="120" y2="10" transform="rotate(180 120 120)"/>
                                <line x1="120" y1="120" x2="120" y2="10" transform="rotate(225 120 120)"/>
                                <line x1="120" y1="120" x2="120" y2="10" transform="rotate(270 120 120)"/>
                                <line x1="120" y1="120" x2="120" y2="10" transform="rotate(315 120 120)"/>
                            </g>
                            <circle cx="120" cy="120" r="9" fill="#1c1917"/>
                        </g>
                        <path d="M168 72 A68 68 0 0 1 188 120" fill="none" stroke="#7c2d12" stroke-width="1.4"/>
                        <text x="196" y="88" fill="#7c2d12" font-size="13" font-family="Instrument Sans, ui-sans-serif, sans-serif">45°</text>
                    </svg>
                </figure>

                <h1 id="judul-beranda" class="mt-8 max-w-[18ch] text-[2rem] font-semibold leading-[1.15] tracking-tight text-tinta sm:text-5xl">
                    Delapan kelopak. Satu putaran 45°.
                </h1>
                <p class="mt-4 max-w-[46ch] text-base leading-relaxed text-tinta-2 sm:text-lg">
                    Prototipe LMS penelitian ini: pembelajaran matematika berdiferensiasi pada transformasi geometri, lewat motif payung geulis, batik Sawoan, dan anyaman Rajapolah. Sasarannya computational thinking. Bukan nilai rapor.
                </p>

                <div class="mt-8 max-w-sm">
                    <a href="{{ route('masuk') }}" class="block rounded-lg bg-aksen px-4 py-3 text-center text-base font-semibold text-white focus:outline-none focus:ring-2 focus:ring-aksen/40 focus:ring-offset-2 focus:ring-offset-latar active:bg-aksen-2">
                        Masuk belajar
                    </a>
                    <p class="mt-3 text-sm leading-relaxed text-tinta-2">
                        Siswa isi NIS dan PIN 6 digit dari kartu guru. Guru, observer, dan peneliti mengetik nama pengguna di kolom yang sama.
                    </p>
                </div>
            </section>

            <ol class="mt-14 border-t border-garis" aria-label="Lima pertemuan">
                <li class="flex gap-4 border-b border-garis py-4">
                    <span class="w-6 shrink-0 tabular-nums text-tinta-3" aria-hidden="true">1</span>
                    <div>
                        <p class="font-semibold text-tinta">Translasi</p>
                        <p class="text-sm text-tinta-2">Pola anyaman Rajapolah</p>
                    </div>
                </li>
                <li class="flex gap-4 border-b border-garis py-4">
                    <span class="w-6 shrink-0 tabular-nums text-tinta-3" aria-hidden="true">2</span>
                    <div>
                        <p class="font-semibold text-tinta">Refleksi</p>
                        <p class="text-sm text-tinta-2">Motif batik Sawoan Tasikmalaya</p>
                    </div>
                </li>
                <li class="flex gap-4 border-b border-garis py-4">
                    <span class="w-6 shrink-0 tabular-nums text-tinta-3" aria-hidden="true">3</span>
                    <div>
                        <p class="font-semibold text-tinta">Rotasi</p>
                        <p class="text-sm text-tinta-2">Rozet payung geulis</p>
                    </div>
                </li>
                <li class="flex gap-4 border-b border-garis py-4">
                    <span class="w-6 shrink-0 tabular-nums text-tinta-3" aria-hidden="true">4</span>
                    <div>
                        <p class="font-semibold text-tinta">Dilatasi</p>
                        <p class="text-sm text-tinta-2">Motif berlapis</p>
                    </div>
                </li>
                <li class="flex gap-4 py-4">
                    <span class="w-6 shrink-0 tabular-nums text-tinta-3" aria-hidden="true">5</span>
                    <div>
                        <p class="font-semibold text-tinta">Komposisi</p>
                        <p class="text-sm text-tinta-2">Proyek motif orisinal</p>
                    </div>
                </li>
            </ol>

            <section class="mt-14 border-t border-garis pt-10" aria-labelledby="judul-identitas">
                <h2 id="judul-identitas" class="text-lg font-semibold text-tinta">Identitas penelitian</h2>
                <p class="mt-3 max-w-[54ch] text-base leading-relaxed text-tinta">
                    Desain Pembelajaran Digital Adaptif dalam Matematika Berbasis Etnomatematika Priangan Timur untuk Mengembangkan Kemampuan Computational Thinking pada Materi Transformasi Geometri
                </p>
                <dl class="mt-6 max-w-[54ch] space-y-3 text-sm leading-relaxed">
                    <div>
                        <dt class="text-tinta-3">Skema</dt>
                        <dd class="text-tinta-2">Penelitian Kompetitif Universitas Siliwangi 2026, Penelitian Pengembangan Kapasitas (PPKap), satu tahun</dd>
                    </div>
                    <div>
                        <dt class="text-tinta-3">Rumpun ilmu</dt>
                        <dd class="text-tinta-2">Pendidikan Matematika, Fakultas Keguruan dan Ilmu Pendidikan, Universitas Siliwangi</dd>
                    </div>
                    <div>
                        <dt class="text-tinta-3">Metode</dt>
                        <dd class="text-tinta-2">Educational Design Research, model Plomp: preliminary research, prototyping, assessment</dd>
                    </div>
                    <div>
                        <dt class="text-tinta-3">Sasaran</dt>
                        <dd class="text-tinta-2">SDG 4 Quality Education. Luaran: prototipe LMS, artikel jurnal, dan hak cipta perangkat lunak</dd>
                    </div>
                </dl>
            </section>

            <section class="mt-14 border-t border-garis pt-10" aria-labelledby="judul-tim">
                <h2 id="judul-tim" class="text-lg font-semibold text-tinta">Tim peneliti</h2>
                <p class="mt-2 max-w-[46ch] text-sm leading-relaxed text-tinta-2">
                    Program Studi Pendidikan Matematika, Universitas Siliwangi. Kepakaran mengikuti laman dosen jurusan dan profil SINTA.
                </p>

                <ul class="mt-6 space-y-6">
                    <li>
                        <p class="text-sm text-tinta-3">Ketua pengusul</p>
                        <p class="font-semibold text-tinta">Depi Ardian Nugraha, S.Pd., M.Pd.</p>
                        <p class="text-sm text-tinta-2">Asesmen dan evaluasi pembelajaran matematika</p>
                        <p class="text-sm text-tinta-3">
                            <a href="https://sinta.kemdiktisaintek.go.id/authors/profile/6683295" class="underline decoration-garis underline-offset-2 hover:text-aksen focus:outline-none focus:ring-2 focus:ring-aksen/40">SINTA 6683295</a>
                        </p>
                    </li>
                    <li>
                        <p class="text-sm text-tinta-3">Anggota peneliti</p>
                        <p class="font-semibold text-tinta">Dr. Eko Yulianto, S.Pd., M.Pd.</p>
                        <p class="text-sm text-tinta-2">Kajian antropologi budaya dalam etnomatematika; statistika penelitian</p>
                        <p class="text-sm text-tinta-3">
                            <a href="https://sinta.kemdiktisaintek.go.id/authors/profile/5983478" class="underline decoration-garis underline-offset-2 hover:text-aksen focus:outline-none focus:ring-2 focus:ring-aksen/40">SINTA 5983478</a>
                        </p>
                    </li>
                    <li>
                        <p class="text-sm text-tinta-3">Anggota peneliti</p>
                        <p class="font-semibold text-tinta">Satya Santika, S.Pd., M.Pd.</p>
                        <p class="text-sm text-tinta-2">Technology-enhanced mathematics education; media pembelajaran</p>
                        <p class="text-sm text-tinta-3">
                            <a href="https://sinta.kemdiktisaintek.go.id/authors/profile/5988016" class="underline decoration-garis underline-offset-2 hover:text-aksen focus:outline-none focus:ring-2 focus:ring-aksen/40">SINTA 5988016</a>
                        </p>
                    </li>
                    <li>
                        <p class="text-sm text-tinta-3">Anggota peneliti</p>
                        <p class="font-semibold text-tinta">Vepi Apiati, S.Pd., M.Pd.</p>
                        <p class="text-sm text-tinta-2">Inkuiri dan matematika realistik berbasis digital</p>
                        <p class="text-sm text-tinta-3">
                            <a href="https://sinta.kemdiktisaintek.go.id/authors/profile/6012475" class="underline decoration-garis underline-offset-2 hover:text-aksen focus:outline-none focus:ring-2 focus:ring-aksen/40">SINTA 6012475</a>
                        </p>
                    </li>
                    <li>
                        <p class="text-sm text-tinta-3">Anggota mahasiswa</p>
                        <p class="font-semibold text-tinta">Reza Mohammad Rizqi</p>
                        <p class="text-sm text-tinta-2">Pendidikan Matematika</p>
                    </li>
                    <li>
                        <p class="text-sm text-tinta-3">Anggota mahasiswa</p>
                        <p class="font-semibold text-tinta">Aufa Dzakiya Aziza</p>
                        <p class="text-sm text-tinta-2">Pendidikan Matematika</p>
                    </li>
                </ul>
            </section>

            <p class="mt-12 max-w-[46ch] text-sm leading-relaxed text-tinta-2">
                Nama di laporan penelitian jadi kode seperti S-001. Validator ahli membuka tautan yang dikirim peneliti, bukan halaman ini.
            </p>
        </main>

        <footer class="mt-12 pb-4 text-xs leading-relaxed text-tinta-3">
            Program Studi Pendidikan Matematika<br>
            Universitas Siliwangi, Jl. Siliwangi No. 24 Tasikmalaya
        </footer>
    </div>
</body>
</html>
