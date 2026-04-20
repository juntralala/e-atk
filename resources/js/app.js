import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import '@mdi/font/css/materialdesignicons.css';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { createVuetify } from 'vuetify';
import { en, id } from 'vuetify/locale';
import 'vuetify/styles';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const vuetify = createVuetify({
    locale: {
        locale: 'id',
        messages: {
            id,
            en,
        },
    },
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(vuetify)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
