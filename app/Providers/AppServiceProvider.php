<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hanya preload vendor/core chunks — jangan preload page-specific chunks
        // yang tidak dibutuhkan di halaman saat ini (hemat 1.5MB+ di homepage)
        Vite::usePreloadTagAttributes(function (string $src, string $url, array $chunk, array $manifest): array|false {
            // Skip preload untuk page-specific chunks — akan dimuat lazy saat navigasi
            if (preg_match('/pages-(account|booking|asset-detail|admin|auth|chat|owner)/', $src)) {
                return false; // tidak inject <link rel="modulepreload"> untuk chunk ini
            }
            return []; // preload normal untuk vendor-core, vendor-misc, app, etc.
        });
    }
}
