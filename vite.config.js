import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js",
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
        visualizer(),
    ],
    resolve: {
        alias: {
            "@assets": fileURLToPath(
                new URL("./resources/assets", import.meta.url)
            ),
            "@composables": fileURLToPath(
                new URL("./resources/js/Composables", import.meta.url)
            ),
        },
    },
    build: {
        minify: true,
        cssCodeSplit: true,
        modulePreload: false,
    },
});
