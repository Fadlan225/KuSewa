    import '../css/app.css';
    import './bootstrap';

    import { createInertiaApp } from '@inertiajs/vue3';
    import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
    import { createApp, h } from 'vue';
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

    createInertiaApp({
        title: (title) => `${title} - ${appName}`,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue'),
            ),
        setup({ el, App, props, plugin }) {
            return createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .mount(el);
        },
        progress: {
            color: '#FFC000',
        },
    });
