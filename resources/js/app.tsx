import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import type { ComponentType } from 'react';
import { ThemeProvider } from '@/components/theme/theme-provider';
import { registerHttpInterceptors } from '@/lib/http';

registerHttpInterceptors();

createInertiaApp({
    resolve: async (name) => {
        const pages = import.meta.glob<{ default: ComponentType }>('./pages/**/*.tsx');
        const page = pages[`./pages/${name}.tsx`];
        if (!page) throw new Error(`Página Inertia não encontrada: ${name}`);
        const module = await page();
        return module.default;
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <ThemeProvider>
                <App {...props} />
            </ThemeProvider>,
        );
    },
    progress: { color: '#0f766e' },
});
