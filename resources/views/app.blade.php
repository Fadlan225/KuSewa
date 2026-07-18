<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.ico') }}">
        <meta property="og:title" content="KuSewa | Platform Penyewaan Aset Tak Bergerak">
        <meta property="og:description" content="Lahan kosong, baliho strategis, apartemen premium, dan ruang komersial dalam satu platform.">
        <meta property="og:image" content="{{ asset('logo.png') }}">
        {{-- <meta property="og:url" content="https://kusewa.com/"> --}}
        <meta property="og:type" content="website">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
