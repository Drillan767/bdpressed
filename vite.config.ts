import vue from '@vitejs/plugin-vue'
import VueRouter from 'unplugin-vue-router/vite'
import { defineConfig } from 'vite'
import vueDevTools from 'vite-plugin-vue-devtools'
import vuetify from 'vite-plugin-vuetify'

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        VueRouter({}),
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
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern',
            },
        },
    },
})
