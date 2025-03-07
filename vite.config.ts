import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'
import vuetify from 'vite-plugin-vuetify'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({
            styles: {
                configFile: 'resources/styles/vuetify.scss',
            },
        }),
    ],
    ssr: {
        noExternal: [
            '@inertiajs/server',
            /\.css$/,
            /\?vue&type=style/,
            /^vuetify/,
        ],
    },
})
