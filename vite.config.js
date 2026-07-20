import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

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
        port: 5173,

        cors: {
            origin: ['http://192.168.18.55:8080',
                    'http://127.0.0.1:8000',
            ]
        },

        hmr: {
            host: '192.168.18.55',
            port: 5173,
        },
    },

});
