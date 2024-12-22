import type { DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { renderToString } from '@vue/server-renderer'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createSSRApp, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import vuetify from '@/plugins/vuetify'
import { createHead } from '@vueuse/head'
import '@/plugins/vee-validate'

import '../styles/main.scss'

const head = createHead()

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
                .use(head)
                .use(ZiggyVue, {
                    ...page.props.ziggy,
                    location: new URL(page.props.ziggy.location),
                })
        },
    }),
)
