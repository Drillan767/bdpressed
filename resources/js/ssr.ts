import type { DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { renderToString } from '@vue/server-renderer'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createSSRApp, h } from 'vue'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import vuetify from '@/plugins/vuetify'

import '../styles/main.scss'

createServer(page =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: name =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
            ),
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(vuetify)
                .use(ZiggyVue, {
                    ...page.props.ziggy,
                    location: new URL(page.props.ziggy.location),
                })
        },
    }),
)
