<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ isset($judul) ? $judul.' · ' : '' }}{{ config('app.name', 'GEULIS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-latar font-sans text-tinta antialiased">
    {{ $slot }}
    @livewireScriptConfig
</body>
</html>
