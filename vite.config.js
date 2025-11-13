import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/booking.js',
                'resources/js/calendar.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Enable minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        // Code splitting
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vendor chunks for better caching
                    'vendor': [
                        'bootstrap',
                    ],
                    'fullcalendar': [
                        '@fullcalendar/core',
                        '@fullcalendar/daygrid',
                        '@fullcalendar/timegrid',
                        '@fullcalendar/interaction',
                    ],
                },
                // Add hash to filenames for cache busting
                assetFileNames: 'assets/[name].[hash][extname]',
                chunkFileNames: 'js/[name].[hash].js',
                entryFileNames: 'js/[name].[hash].js',
            },
        },
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
        // Enable source maps for production debugging (optional)
        sourcemap: false,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['bootstrap'],
    },
});
