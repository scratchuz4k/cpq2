import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy'
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        viteStaticCopy({
            targets: [
                {
                    src: fileURLToPath(new URL('./resources/assets', import.meta.url)),
                    dest: fileURLToPath(new URL('./public', import.meta.url))
                }
            ]
        }),
        laravel({
            input: 'resources/js/app.js',
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
        
    ],
    resolve: {
        alias: {
            '@assets': fileURLToPath(new URL('./resources/assets', import.meta.url)),
            '@composables': fileURLToPath(new URL('./resources/js/Composables', import.meta.url)),
        }
    },
});
