import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

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
        host: '0.0.0.0',
        port: parseInt(process.env.VITE_PORT || '5173'),
        strictPort: true,
        cors: {
            origin: [
                `http://${hmrHost}:8080`,
                'http://127.0.0.1:8000',
                'http://127.0.0.1:8080',
            ],
        },
        hmr: {
            host: hmrHost,
            port: hmrPort,
        },
    },

    build: {
        chunkSizeWarningLimit: 600,

        rollupOptions: {
            output: {
                manualChunks(id) {
                    // ─── VENDOR: Vue core + Inertia + Ziggy ───────────────────────────
                    if (
                        id.includes('node_modules/vue/') ||
                        id.includes('node_modules/@vue/') ||
                        id.includes('node_modules/@inertiajs/') ||
                        id.includes('vendor/tightenco/ziggy')
                    ) {
                        return 'vendor-core';
                    }

                    // ─── VENDOR: Lucide icons (besar, pisahkan) ───────────────────────
                    if (id.includes('node_modules/lucide-vue-next')) {
                        return 'vendor-icons';
                    }

                    // ─── VENDOR: Flatpickr date picker ────────────────────────────────
                    if (
                        id.includes('node_modules/flatpickr') ||
                        id.includes('node_modules/vue-flatpickr-component')
                    ) {
                        return 'vendor-datepicker';
                    }

                    // ─── VENDOR: VueUse ───────────────────────────────────────────────
                    if (id.includes('node_modules/@vueuse/')) {
                        return 'vendor-vueuse';
                    }

                    // ─── VENDOR: Pinia ────────────────────────────────────────────────
                    if (id.includes('node_modules/pinia')) {
                        return 'vendor-pinia';
                    }

                    // ─── VENDOR: axios ────────────────────────────────────────────────
                    if (id.includes('node_modules/axios')) {
                        return 'vendor-axios';
                    }

                    // ─── VENDOR: Laravel Echo / Pusher / Reverb ───────────────────────
                    if (
                        id.includes('node_modules/laravel-echo') ||
                        id.includes('node_modules/pusher-js')
                    ) {
                        return 'vendor-echo';
                    }

                    // ─── VENDOR: sisa library lainnya ─────────────────────────────────
                    if (id.includes('node_modules/')) {
                        return 'vendor-misc';
                    }

                    // ─── PAGES: Admin ─────────────────────────────────────────────────
                    if (id.includes('/Pages/admin/')) {
                        return 'pages-admin';
                    }

                    // ─── PAGES: Owner ─────────────────────────────────────────────────
                    if (id.includes('/Pages/owner/')) {
                        return 'pages-owner';
                    }

                    // ─── PAGES: Auth ──────────────────────────────────────────────────
                    if (id.includes('/Pages/Auth/')) {
                        return 'pages-auth';
                    }

                    // ─── PAGES: Chat (heavy: Echo, axios, realtime) ───────────────────
                    if (id.includes('/Pages/Home/Chat/')) {
                        return 'pages-chat';
                    }

                    // ─── PAGES: Booking & Payment ─────────────────────────────────────
                    if (
                        id.includes('/Pages/Home/Bookings/') ||
                        id.includes('/Pages/Home/Payment')
                    ) {
                        return 'pages-booking';
                    }

                    // ─── PAGES: Asset detail & search ─────────────────────────────────
                    if (id.includes('/Pages/Home/Assets/')) {
                        return 'pages-asset-detail';
                    }

                    // ─── PAGES: Activity, Notifications, Profile ──────────────────────
                    if (
                        id.includes('/Pages/Home/Activity/') ||
                        id.includes('/Pages/Notifications/') ||
                        id.includes('/Pages/Profile/')
                    ) {
                        return 'pages-account';
                    }

                    // ─── PAGES: Lainnya (Home index, Favorite, Reviews, Support) ──────
                    if (id.includes('/Pages/Home/')) {
                        return 'pages-home';
                    }
                },
            },
        },
    },

    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/vue3',
        ],
    },
});
