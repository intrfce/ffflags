import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { resolve } from 'path'

export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        outDir: 'dist',
        rollupOptions: {
            input: resolve(__dirname, 'resources/js/app.js'),
            output: {
                entryFileNames: 'ffflags.js',
                assetFileNames: 'ffflags.[ext]',
            },
        },
    },
})
