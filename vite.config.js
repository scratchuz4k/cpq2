import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import svgSpritePlugin from '@pivanov/vite-plugin-svg-sprite';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false
                }
            }
        }),
        svgSpritePlugin({
            iconDirs: ['resources/assets/icons'],
            symbolId: 'icon-[name]',
            fileName: 'icons-sprite.svg'
        })
    ],
    resolve: {
        alias: {
            '@assets': fileURLToPath(new URL('./resources/assets', import.meta.url)),
            '@composables': fileURLToPath(new URL('./resources/js/Composables', import.meta.url)),
            '@public': fileURLToPath(new URL('./public/', import.meta.url))
        }
    },
    build: {
        minify: true,
        cssCodeSplit: true,
        modulePreload: false
    },
    server: {
        host: '0.0.0.0',
        port: 5299,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
            port: 5299,
        },
    },
});
