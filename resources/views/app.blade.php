<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="icon" type="image/svg+xml" href="{{ asset('kitasewa-logo.png') }}">

        <!-- Preload LCP hero image — browser fetch sebelum JS selesai render -->
        <link rel="preload" as="image" href="/public.webp" fetchpriority="high">
        <!-- Preload logo agar tidak CLS -->
        <link rel="preload" as="image" href="/kitasewa-logo.png">

        {{--
            Critical CSS — inline agar browser bisa paint static-hero-placeholder
            SEBELUM app.css selesai didownload (app.css adalah render-blocking).
            Hanya berisi style minimal yang dibutuhkan oleh #static-hero-placeholder.
        --}}
        <style>
            *, *::before, *::after { box-sizing: border-box; }
            body { margin: 0; font-family: ui-sans-serif, system-ui, sans-serif; background: #f9fafb; }
            #static-hero-placeholder {
                position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
                width: 100%; height: 360px; overflow: hidden; background: #0A2540;
                will-change: opacity; transition: opacity 0.2s ease-out;
            }
        </style>

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

        <!-- Google Analytics 4 — dimuat setelah load event agar tidak blok render -->
        @if(config('services.google_analytics.measurement_id'))
        <script>
            window.addEventListener('load', function() {
                var gaMeasurementId = '{{ config('services.google_analytics.measurement_id') }}';
                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=' + gaMeasurementId;
                document.head.appendChild(script);
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                window.gtag = gtag;
                gtag('js', new Date());
                gtag('config', gaMeasurementId, { send_page_view: false });
            });
        </script>
        @endif
    </head>
    <body class="font-sans antialiased">

        {{-- Static LCP Hero Placeholder --}}
        {{-- position:fixed agar tidak mendorong konten Vue ke bawah (no layout shift) --}}
        {{-- Dicabut dengan fade setelah Vue mount. LCP diukur dari img ini (~1-2s) --}}
        <div id="static-hero-placeholder" aria-hidden="true"
             style="position:fixed;top:0;left:0;right:0;z-index:9999;width:100%;height:360px;overflow:hidden;background:#0A2540;"
        >
            <img
                src="/public.webp"
                alt="KitaSewa - Platform Sewa Aset Terpercaya"
                fetchpriority="high"
                decoding="async"
                width="1440"
                height="540"
                style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;"
            >
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);"></div>
            <div style="position:absolute;inset:0;max-width:80rem;margin:0 auto;padding:0 1.5rem;display:flex;flex-direction:column;justify-content:center;">
                <h1 style="font-size:clamp(1.25rem,3.5vw,3rem);font-weight:800;color:white;line-height:1.25;margin:0;">
                    Temukan <span style="color:#FFC000;">Aset,</span><br>
                    <span style="color:#FFC000;">Wujudkan</span> Rencana
                </h1>
                <p style="color:rgba(255,255,255,0.8);margin-top:0.75rem;font-size:clamp(0.75rem,1.5vw,0.9rem);max-width:36rem;">
                    Butuh tempat untuk mewujudkan rencana? Temukan aset yang tepat di KitaSewa.
                </p>
            </div>
        </div>

        @inertia
    </body>
</html>
