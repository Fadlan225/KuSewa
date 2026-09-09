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
            
            /* Animasi Skeleton Pulse */
            @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .6; } }
            .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
            .sk-box { background: rgba(255,255,255,0.25); border-radius: 8px; }

            /* Splash Screen Full untuk menutupi proses render Vue */
            #static-hero-placeholder {
                position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
                width: 100%; height: 100vh; background: #f9fafb;
                will-change: opacity; transition: opacity 0.3s ease-out; pointer-events: none;
            }

            /* Responsive Hero Layout (meniru class Tailwind di HeroSection.vue) */
            .hero-wrapper { width: 100%; padding: 0.75rem 0.75rem 1.5rem; position: relative; }
            .hero-container { position: relative; width: 100%; height: 360px; border-radius: 1rem; overflow: hidden; background: #0A2540; }
            .hero-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); z-index: 5; }
            .hero-content { position: relative; z-index: 10; max-width: 80rem; margin: 0 auto; height: 100%; display: flex; flex-direction: column; justify-content: center; padding: 0 1.5rem; }
            
            /* Elemen Skeleton di dalam Hero */
            .sk-title { width: 90%; height: 60px; max-width: 400px; margin: 0 auto 12px; }
            .sk-subtitle { width: 80%; height: 20px; max-width: 350px; margin: 0 auto 24px; }
            .sk-search { width: 100%; max-width: 384px; height: 44px; border-radius: 9999px; background: rgba(255,255,255,0.8); margin: 0 auto; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
            
            /* Navbar Skeleton */
            .nav-skeleton { position: absolute; top: 0; left: 0; right: 0; height: 64px; z-index: 20; padding: 0 1.5rem; display: flex; align-items: center; justify-content: space-between; max-width: 80rem; margin: 0 auto; }
            .sk-logo { width: 120px; height: 32px; }
            .sk-menu { display: none; }

            /* Breakpoints */
            @media (min-width: 640px) { /* sm */
                .hero-wrapper { padding: 1.5rem 1.5rem 1.5rem; }
                .hero-container { height: 420px; }
                .nav-skeleton { padding: 0 2.5rem; }
                .sk-title { height: 70px; max-width: 500px; }
            }
            @media (min-width: 768px) { /* md */
                .hero-wrapper { padding: 0; }
                .hero-container { height: 500px; border-radius: 0; }
                .hero-content { padding: 0 2.5rem; align-items: flex-start; text-align: left; }
                .sk-title { height: 90px; max-width: 600px; margin: 0 0 16px 0; }
                .sk-subtitle { margin: 0 0 32px 0; max-width: 500px; }
                .sk-search { margin: 0; max-width: 800px; height: 60px; }
                .sk-menu { display: flex; gap: 20px; }
                .sk-menu-item { width: 80px; height: 20px; }
            }
            @media (min-width: 1024px) { /* lg */
                .hero-container { height: 540px; }
                .hero-content { padding: 0 2rem; }
                .nav-skeleton { padding: 0 2rem; }
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
        {{-- Full height screen, dicabut dengan fade setelah Vue mount --}}
        <div id="static-hero-placeholder" aria-hidden="true">
            
            <div class="hero-wrapper">
                <div class="hero-container">
                    
                    <!-- LCP Image Indicator -->
                    <img
                        src="/public.webp"
                        alt="Background"
                        fetchpriority="high"
                        decoding="async"
                        width="1440"
                        height="540"
                        style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;"
                    >
                    
                    <div class="hero-overlay"></div>
                    
                    <!-- Navbar Skeleton -->
                    <div class="nav-skeleton animate-pulse">
                        <div class="sk-box sk-logo"></div>
                        <div class="sk-menu">
                            <div class="sk-box sk-menu-item"></div>
                            <div class="sk-box sk-menu-item"></div>
                            <div class="sk-box sk-menu-item"></div>
                            <div class="sk-box sk-menu-item" style="width:100px; border-radius:9999px;"></div>
                        </div>
                    </div>

                    <!-- Hero Content Skeleton -->
                    <div class="hero-content animate-pulse">
                        <div class="sk-box sk-title"></div>
                        <div class="sk-box sk-subtitle"></div>
                        <div class="sk-search"></div>
                    </div>
                </div>
            </div>

            <!-- Bagian list aset di bawah (hanya kotak-kotak loading abu-abu) -->
            <div style="max-w: 80rem; margin: 0 auto; padding: 2rem 1.5rem;" class="animate-pulse">
                <div class="sk-box" style="width: 150px; height: 24px; margin-bottom: 2rem; background: #e5e7eb;"></div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div class="sk-box" style="height: 300px; background: #e5e7eb; border-radius: 12px;"></div>
                    <div class="sk-box" style="height: 300px; background: #e5e7eb; border-radius: 12px; display: none; @media(min-width: 640px){display: block;}"></div>
                    <div class="sk-box" style="height: 300px; background: #e5e7eb; border-radius: 12px; display: none; @media(min-width: 768px){display: block;}"></div>
                    <div class="sk-box" style="height: 300px; background: #e5e7eb; border-radius: 12px; display: none; @media(min-width: 1024px){display: block;}"></div>
                </div>
            </div>
        </div>

        @inertia
    </body>
</html>
