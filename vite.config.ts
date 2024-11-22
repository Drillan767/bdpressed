import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue'
import { VueUseComponentsResolver, VueUseDirectiveResolver } from 'unplugin-vue-components/resolvers'
import Components from 'unplugin-vue-components/vite'
import vueRouter from 'unplugin-vue-router/vite'
import { defineConfig } from 'vite'
import vueDevTools from 'vite-plugin-vue-devtools'
import vuetify from 'vite-plugin-vuetify'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        vueRouter({
            logs: true,
            routesFolder: [{
                path: '',
                src: './src/pages',
            }],
            extensions: ['.vue'],
            dts: './typed-router.d.ts',
            importMode: 'async',
        }),
        vue(),
        Components({
            dts: true,
            resolvers: [
                VueUseComponentsResolver(),
                VueUseDirectiveResolver(),
            ],
        }),
        vuetify(),
        vueDevTools(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
            '@root': fileURLToPath(new URL('./', import.meta.url)),
        },
    },
})
