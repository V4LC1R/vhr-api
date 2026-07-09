import { useEffect } from 'react';

import { useUIStore, type Theme } from '@/stores/ui';

const MEDIA_QUERY = '(prefers-color-scheme: dark)';

/** Aplica (ou remove) a classe `.dark` no <html> conforme o tema escolhido. */
function applyTheme(theme: Theme) {
    if (typeof document === 'undefined') return;

    const isDark =
        theme === 'dark' ||
        (theme === 'system' && window.matchMedia(MEDIA_QUERY).matches);

    document.documentElement.classList.toggle('dark', isDark);

    const favicon = document.getElementById('favicon') as HTMLLinkElement | null;
    if (favicon) favicon.href = isDark ? '/favicon-light.svg' : '/favicon-dark.svg';
}

/** Lê/atualiza o tema persistido no store da UI. */
export function useTheme() {
    const theme = useUIStore((state) => state.theme);
    const setTheme = useUIStore((state) => state.setTheme);

    return { theme, setTheme };
}

/**
 * Efeito global do tema: reaplica a classe quando o tema muda e acompanha a
 * preferência do sistema operacional enquanto o modo "system" estiver ativo.
 */
export function useApplyTheme() {
    const theme = useUIStore((state) => state.theme);

    useEffect(() => {
        applyTheme(theme);
    }, [theme]);

    useEffect(() => {
        if (theme !== 'system') return;

        const media = window.matchMedia(MEDIA_QUERY);
        const onChange = () => applyTheme('system');

        media.addEventListener('change', onChange);
        return () => media.removeEventListener('change', onChange);
    }, [theme]);
}
