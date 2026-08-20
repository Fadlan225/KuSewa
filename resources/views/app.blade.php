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
        <meta name="title" content="KitaSewa | Temukan Aset, Wujudkan Rencana">
        <meta name="description" content="Butuh tempat untuk mewujudkan rencana? Temukan aset yang tepat di KitaSewa.">

        <!-- Open Graph / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://kitasewa.web.id/">
        <meta property="og:title" content="KitaSewa | Temukan Aset, Wujudkan Rencana">
        <meta property="og:description" content="Butuh tempat untuk mewujudkan rencana? Temukan aset yang tepat di KitaSewa.">
        <meta property="og:image" content="https://kitasewa.web.id/OG-image.jpg">
        <meta property="og:image:secure_url" content="https://kitasewa.web.id/OG-image.jpg">
        <meta property="og:image:type" content="image/jpeg">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="KitaSewa | Temukan Aset, Wujudkan Rencana">
        <meta name="twitter:description" content="Butuh tempat untuk mewujudkan rencana? Temukan aset yang tepat di KitaSewa.">
        <meta name="twitter:image" content="https://kitasewa.web.id/OG-image.jpg">

        <!-- Scripts -->
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
