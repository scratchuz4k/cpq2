import './bootstrap';
import '../scss/app.scss';
import * as bootstrap from 'bootstrap'

// Import all of Bootstrap's JS

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from "primevue/config";
import Aura from '@primevue/themes/aura';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
/* import specific icons */
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'

/* add icons to the library */
library.add(fas)
library.add(far)
library.add(fab)

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {


        let app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        app.component('font-awesome-icon', FontAwesomeIcon)

        const components = {
            install: (app) => {
                const components = import.meta.glob("./Components/**/*.vue", {
                    eager: true
                })
                Object.entries(components).forEach(([path, definition]) => {
                    const componentName = path
                        .split("/")
                        .pop()
                        .replace(/\.\w+$/, "")
                    app.component(componentName, definition.default)
                })
            }
        }
        app.use(components)

        const views = {
            install: (app) => {
                const components = import.meta.glob("./Views/**/*.vue", {
                    eager: true
                })
                Object.entries(components).forEach(([path, definition]) => {
                    const componentName = path
                        .split("/")
                        .pop()
                        .replace(/\.\w+$/, "")
                    app.component(componentName, definition.default)
                })
            }
        }
        app.use(views)

        app.use(PrimeVue, {
            theme: {
                preset: Aura
            }
        });

        app.config.globalProperties.GBP = new Intl.NumberFormat("en-GB", {
            style: "currency",
            currency: "GBP",
        });


        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
