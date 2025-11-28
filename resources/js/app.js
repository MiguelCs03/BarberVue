import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Initialize theme and accessibility on mount
        app.mixin({
            mounted() {
                if (this.$el === el) {
                    // Only run once on root component
                    import('./composables/useTheme').then(({ useTheme }) => {
                        const { initializeTheme } = useTheme();
                        initializeTheme();
                    });

                    import('./composables/useAccessibility').then(({ useAccessibility }) => {
                        const { initializeAccessibility } = useAccessibility();
                        initializeAccessibility();
                    });
                }
            },
        });

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});



// import '../css/app.css';
// import './bootstrap';

// import { createInertiaApp } from '@inertiajs/vue3';
// import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
// import { createApp, h } from 'vue';
// import { ZiggyVue } from 'ziggy-js';

// const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// createInertiaApp({
//     title: (title) => `${title} - ${appName}`,
//     resolve: (name) =>
//         resolvePageComponent(
//             `./Pages/${name}.vue`,
//              import.meta.glob('./Pages/**/*.vue')),
//     // setup({ el, App, props, plugin }) {
//     //     return createApp({ render: () => h(App, props) })
//     //         .use(plugin)
//     //         .use(ZiggyVue) // Ziggy funciona automáticamente con @routes
//     //         .mount(el);
//     // },
//     setup({ el, App, props, plugin }) {
//         const app = createApp({ render: () => h(App, props) })
//             .use(plugin)
//             .use(ZiggyVue); // Ziggy OK
    
//         // Theme & accessibility
//         app.mixin({
//             mounted() {
//                 if (this.$el === el) {
//                     import('./composables/useTheme').then(({ useTheme }) => {
//                         const { initializeTheme } = useTheme();
//                         initializeTheme();
//                     });
    
//                     import('./composables/useAccessibility').then(({ useAccessibility }) => {
//                         const { initializeAccessibility } = useAccessibility();
//                         initializeAccessibility();
//                     });
//                 }
//             },
//         });
    
//         return app.mount(el);
//     }
    
// });

