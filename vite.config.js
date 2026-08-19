import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/receipt.js', 'resources/js/labels.js', 'resources/js/dashboard-charts.js'],
            refresh: true,
        }),
        VitePWA({
            strategies: 'injectManifest',
            srcDir: 'resources/js',
            filename: 'sw.js',
            manifest: false,
            injectRegister: null,
            registerType: 'autoUpdate',
            devOptions: { enabled: false },
            injectManifest: {
                // globDirectory is 'public' (not 'public/build') so precache URLs come out
                // as 'build/assets/...' — the SW is served from '/service-worker.js' at the
                // site root (for root scope), so relative precache URLs resolve against
                // that root and must include the 'build/' prefix to hit the real files.
                globDirectory: 'public',
                globPatterns: ['build/assets/**/*.{js,css,woff2}'],
                additionalManifestEntries: [{ url: '/offline.html', revision: '1' }],
            },
        }),
    ],
});
