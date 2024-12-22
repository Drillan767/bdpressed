import type { DefineComponent } from 'vue'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { createHead } from '@vueuse/head'
import vuetify from '@/plugins/vuetify'
import { ZiggyVue } from 'ziggy-js'
import '@/plugins/vee-validate'

import '../styles/main.scss'

const head = createHead()

createInertiaApp({
    resolve: name =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(vuetify)
            .use(head)
            .mount(el)
    },
    progress: {
        color: '#ff802b',
    },
})
