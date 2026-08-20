<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="icon" type="image/svg+xml" href="{{ asset('kitasewa-logo.png') }}">
        
        <!-- Standard Meta -->
        <meta name="title" content="KitaSewa | Platform Penyewaan Aset Tak Bergerak">
        <meta name="description" content="Lahan kosong, baliho strategis, apartemen premium, dan ruang komersial dalam satu platform.">

        <!-- Open Graph / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://kitasewa.web.id/">
        <meta property="og:title" content="KitaSewa | Platform Penyewaan Aset Tak Bergerak">
        <meta property="og:description" content="Lahan kosong, baliho strategis, apartemen premium, dan ruang komersial dalam satu platform.">
        <!-- Menggunakan URL absolut untuk memastikan gambar terbaca oleh WhatsApp walau APP_URL server salah -->
        <meta property="og:image" content="https://kitasewa.web.id/kitasewa-logo.png">
        <meta property="og:image:secure_url" content="https://kitasewa.web.id/kitasewa-logo.png">
        <meta property="og:image:type" content="image/png">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="KitaSewa | Platform Penyewaan Aset Tak Bergerak">
        <meta name="twitter:description" content="Lahan kosong, baliho strategis, apartemen premium, dan ruang komersial dalam satu platform.">
        <meta name="twitter:image" content="https://kitasewa.web.id/kitasewa-logo.png">

        <!-- Scripts -->
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
