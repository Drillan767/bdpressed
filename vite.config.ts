import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import vueDevTools from 'vite-plugin-vue-devtools'
import vuetify from 'vite-plugin-vuetify'

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        vuetify({
            styles: {
                configFile: 'src/assets/variables.scss',
            },
        }),
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
