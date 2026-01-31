import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'

export default defineConfig({
    server: {
        host: true,
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: '10.24.7.180',
            port: 5173,
        },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
})
