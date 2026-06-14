import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        return pages[`./Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    // Loading bar antar-halaman SPA: NProgress (bawaan @inertiajs/core)
    progress: {
        color: '#6777ef',
        showSpinner: true,
        delay: 100,
    },
});
