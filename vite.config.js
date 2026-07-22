import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

// IP yang diisi oleh scripts/network.js lewat env variable.
// Kalau tidak ada (mode dev lokal), fallback ke 127.0.0.1
const hmrHost = process.env.VITE_HMR_HOST || '127.0.0.1';
const hmrPort = parseInt(process.env.VITE_HMR_PORT || '5173');

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],

    server: {
        // Selalu listen di semua interface agar bisa diakses dari luar
        host: '0.0.0.0',
        port: 5173,

        // CORS dinamis: izinkan origin dari IP saat ini + localhost
        cors: {
            origin: [
                `http://${hmrHost}:8080`,
                'http://127.0.0.1:8000',
                'http://127.0.0.1:8080',
            ],
        },

        // HMR dinamis: browser akan konek ke IP yang dikirim server
        hmr: {
            host: hmrHost,
            port: hmrPort,
        },
    },

    build: {
        // Tingkatkan limit warning chunk size (default 500KB terlalu kecil)
        chunkSizeWarningLimit: 1000,

        rollupOptions: {
            output: {
                // Manual chunk splitting untuk kontrol penuh
                manualChunks(id) {
                    // Vendor: Vue core + Inertia + Ziggy (jarang berubah → cache lama)
                    if (id.includes('node_modules/vue/') ||
                        id.includes('node_modules/@vue/') ||
                        id.includes('node_modules/@inertiajs/') ||
                        id.includes('vendor/tightenco/ziggy')) {
                        return 'vendor-core';
                    }

                    // Vendor UI utilities
                    if (id.includes('node_modules/')) {
                        return 'vendor-misc';
                    }

                    // Halaman Home (besar, bisa di-split terpisah)
                    if (id.includes('/Pages/Home/')) {
                        return 'page-home';
                    }

                    // Halaman Assets/Search
                    if (id.includes('/Pages/Home/Assets/')) {
                        return 'page-assets';
                    }

                    // UI Components (cards, dll)
                    if (id.includes('/Components/UI/')) {
                        return 'ui-components';
                    }
                },
            },
        },
    },

    // Pre-bundle dependensi untuk dev server lebih cepat
    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/vue3',
        ],
        // Exclude file yang tidak perlu di-bundle oleh vite optimizer
        exclude: [],
    },
});
