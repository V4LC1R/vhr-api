import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import type { ComponentType } from 'react';
import { Toaster } from 'react-hot-toast';
import { QueryClientProvider } from '@tanstack/react-query';
import { ThemeProvider } from '@/components/theme/theme-provider';
import { registerHttpInterceptors } from '@/lib/http';
import { queryClient } from '@/lib/query-client';

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
            <QueryClientProvider client={queryClient}>
                <ThemeProvider>
                    <App {...props} />
                    <Toaster position="top-right" />
                </ThemeProvider>
            </QueryClientProvider>,
        );
    },
    progress: { color: '#0f766e' },
});
