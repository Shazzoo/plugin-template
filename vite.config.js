import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    base: '/plugin-builds/plugin-name/',
    resolve: {
        dedupe: ['alpinejs'],
    },
    plugins: [
        laravel({
            input: [
                'storage/app/plugins/plugin-name/resources/css/app.css',
                'storage/app/plugins/plugin-name/resources/js/app.js',
            ],
            publicDirectory: 'public',
            buildDirectory: 'plugin-builds/plugin-name',
            hotFile: 'public/plugin-builds/plugin-name/hot',
            refresh: false,
        }),
    ],
    css: {
        postcss: path.resolve(__dirname, 'postcss.config.js'),
    },
    build: {
        outDir: path.resolve(__dirname, '../../../../public/plugin-builds/plugin-name'),
        emptyOutDir: false,
        assetsDir: 'assets',
    },
});
