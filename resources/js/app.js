import '../css/app.css';
import './bootstrap';
import './echo';

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

const appName = import.meta.env.VITE_APP_NAME || 'kuSewa';

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
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#FFC000',
    },
});
