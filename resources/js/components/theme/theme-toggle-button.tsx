import { Moon, Sun } from 'lucide-react';

import { useTheme } from '@/hooks/use-theme';
import { cn } from '@/lib/utils';

/** Botão quadrado que alterna direto entre claro e escuro. */
export function ThemeToggleButton({ className }: { className?: string }) {
    const { theme, setTheme } = useTheme();

    const isDark =
        theme === 'dark' ||
        (theme === 'system' &&
            window.matchMedia('(prefers-color-scheme: dark)').matches);

    return (
        <button
            type="button"
            aria-label="Alternar tema"
            onClick={() => setTheme(isDark ? 'light' : 'dark')}
            className={cn(
                'flex size-10 items-center justify-center rounded-md bg-primary text-primary-foreground transition-opacity hover:opacity-90',
                className,
            )}
        >
            <Sun className="size-4 dark:hidden" />
            <Moon className="hidden size-4 dark:block" />
        </button>
    );
}
