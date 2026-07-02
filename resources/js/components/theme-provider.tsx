import type { PropsWithChildren } from 'react';

import { useApplyTheme } from '@/hooks/use-theme';

/** Mantém a classe `.dark` sincronizada com o tema escolhido em toda a app. */
export function ThemeProvider({ children }: PropsWithChildren) {
    useApplyTheme();
    return <>{children}</>;
}
