import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import vueDevTools from 'vite-plugin-vue-devtools'
import vuetify from 'vite-plugin-vuetify'

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        vuetify(),
        vueDevTools(),
    ],
    resolve: {
        alias: {
            '@': '/src',
            '@root': '/',
        },
    },

})
