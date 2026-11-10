import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import commonjs from 'vite-plugin-commonjs';
import path from 'path';
import particleIcons from './resources/js/particles/vite-icons.js';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/base.css',
                'resources/scss/app.scss',
                'resources/js/app.js',
                'resources/images/favicon.png',
                'resources/images/bg_dev.png',
            ],
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
        commonjs(),
        particleIcons(),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
            '~fontawesome': path.resolve(__dirname, 'node_modules/@fortawesome/fontawesome-pro'),
        }
    }
});