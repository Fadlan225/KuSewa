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
        // yang tidak dibutuhkan di halaman saat ini (hemat 1.5MB+ di homepage).
        // CATATAN: Laravel Vite bisa memanggil closure ini dengan $src/$chunk/$manifest = null
        // untuk entry CSS-only di manifest, jadi semua parameter tidak boleh pakai type hint.
        Vite::usePreloadTagAttributes(function ($src, $url, $chunk, $manifest): array|false {
            // Guard: skip jika salah satu parameter null (CSS-only manifest entry)
            if ($src === null || $chunk === null) {
                return [];
            }
            // Skip preload untuk page-specific chunks — dimuat lazy saat navigasi
            if (preg_match('/pages-(account|booking|asset-detail|admin|auth|chat|owner)/i', $src)) {
                return false;
            }
            return [];
        });
    }
}
