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
});
