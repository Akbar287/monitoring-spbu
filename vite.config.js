import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

// Vite hanya membangun bundel Inertia/React (resources/js/inertia/*).
// Aset legacy (resources/js/app.js + sass) tetap dibangun oleh Laravel Mix
// ke public/js & public/css selama masa transisi Blade -> Inertia.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/inertia/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
});
