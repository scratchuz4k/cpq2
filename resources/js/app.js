import './bootstrap';
import '../scss/app.scss';
import { tooltip } from './Composables/tooltip'
import * as Sentry from "@sentry/vue";


// Import all of Bootstrap's JS

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from "primevue/config";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

import { Notyf } from 'notyf';
import 'notyf/notyf.min.css'; // for React, Vue and Svelte

window.notyf = new Notyf({
    duration: 5000,
    ripple: false,
    dismissible: true,
    position: {
        x: "right",
        y: "top",
    },
});

window.notyf = notyf;


import mitt from 'mitt'

window.emitter = mitt()

import Swal from "sweetalert2";
window.swal = Swal.mixin({
    buttonsStyling: true,
    showCancelButton: true,
    confirmButtonColor: '#ec008c',
    width: '20em',
    customClass: {
        confirmButton: 'btn text-light btn-lg mr-2 fs-6',
        cancelButton: 'btn btn-outline-primary border-light-purple btn-lg fs-6',
        loader: 'custom-loader',
        title: 'h3'
    }
}
)


/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
/* import specific icons */
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import AuraReformed from './Themes/Aura';

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

        app.directive('tooltip', tooltip)
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
                preset: AuraReformed,
                options: {
                    darkModeSelector: '.dark-mode',
                }
            }
        });

        app.config.globalProperties.GBP = new Intl.NumberFormat("en-GB", {
            style: "currency",
            currency: "GBP",
        });

        Sentry.init({
            app,
            environment: "development",
            dsn: "https://ff2c64612a62f755721e6d382b9b87fb@o4508149150908416.ingest.de.sentry.io/4508149152350288",
            integrations: [
                Sentry.browserTracingIntegration(),
                Sentry.replayIntegration({
                    maskAllText: false,
                    blockAllMedia: false,
                    networkDetailAllowUrls: [window.location.origin],
                    networkRequestHeaders: ["Cache-Control"],
                    networkResponseHeaders: ["Referrer-Policy"],
                }),
                Sentry.feedbackIntegration({
                    showBranding: false,
                    useSentryUser: {
                        name: "fullName",
                        email: "email",
                    },
                    // Additional SDK configuration goes in here, for example:
                    colorScheme: "system",
                }),
            ],
            // Tracing
            tracesSampleRate: 1.0, //  Capture 100% of the transactions
            // Set 'tracePropagationTargets' to control for which URLs distributed tracing should be enabled
            tracePropagationTargets: ["localhost","*.cloudmarket.com"],///^https:\/\/yourserver\.io\/api/
            // Session Replay
            replaysSessionSampleRate: 0.1, // This sets the sample rate at 10%. You may want to change it to 100% while in development and then sample at a lower rate in production.
            replaysOnErrorSampleRate: 1.0, // If you're not already sampling the entire session, change the sample rate to 100% when sampling sessions where errors occur.
        });


        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
}).then(() => {
    document.getElementById('app').removeAttribute('data-page');
});
