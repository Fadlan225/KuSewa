import '../css/app.css';
import './bootstrap';
// echo.js di-lazy load hanya untuk user yang sudah login (lihat setup() di bawah)

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { router } from '@inertiajs/vue3';
import {
    showLoading,
    hideLoading,
} from './Stores/loading';

const appName = import.meta.env.VITE_APP_NAME || 'kitaSewa';

let timer = null;


router.on('start', () => {

    timer = setTimeout(() => {

        showLoading();

    }, 400);

});


router.on('finish', () => {

    if(timer){
        clearTimeout(timer);
        timer = null;
    }

    hideLoading();

});

// Google Analytics 4 – catat page view setiap navigasi Inertia
router.on('navigate', (event) => {
    if (typeof gtag === 'function') {
        gtag('event', 'page_view', {
            page_title: document.title,
            page_location: window.location.href,
            page_path: event.detail.page.url,
        });
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue', { eager: false }),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .use(ZiggyVue)
            .mount(el);

        // Lazy-init Echo + Pusher HANYA untuk user yang sudah login
        // Menghemat ~80-120 KB JS parse untuk tamu (mayoritas traffic)
        if (props.initialPage?.props?.auth?.user) {
            import('./echo').catch(() => {});
        }

        // Cabut static hero placeholder setelah Vue selesai mount + first paint
        // Langsung remove tanpa fade — mencegah overlap DOM yang menyebabkan LCP bergeser
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                document.getElementById('static-hero-placeholder')?.remove();
            });
        });

        return vueApp;
    },
    progress: {
        color: '#FFC000',
    },
});
