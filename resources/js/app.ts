import type { DefineComponent } from 'vue'

import vuetify from '@/Plugins/vuetify'
import { createInertiaApp } from '@inertiajs/vue3'
import { createHead } from '@vueuse/head'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import piniaPersistedState from 'pinia-plugin-persistedstate'
import { createApp, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import '@/Plugins/vee-validate'

import '../styles/main.scss'

const pinia = createPinia()
pinia.use(piniaPersistedState)

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
            .use(pinia)
            .use(head)
            .mount(el)
    },
    progress: {
        color: '#ff802b',
    },
})
