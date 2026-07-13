import { useMemo } from 'react';
import { isValid, parse } from 'date-fns';

import { Employment } from '@/types/employment/types';

/**
 * As datas do vínculo chegam em ISO 8601 UTC, mas só a parte da data importa —
 * parsear com hora/fuso jogaria admissão/desligamento pro dia anterior no fuso local.
 */
function parseDateOnly(value: string | null | undefined): Date | null {
    if (!value) return null;

    const date = parse(value.slice(0, 10), 'yyyy-MM-dd', new Date());

    return isValid(date) ? date : null;
}

/** Período do vínculo (admissão → desligamento) como datas locais. */
export function useEmploymentPeriod(employment: Employment | null | undefined) {
    return useMemo(
        () => ({
            /** Data de admissão; hoje enquanto nenhum vínculo estiver carregado. */
            registerDate: parseDateOnly(employment?.registerAt) ?? new Date(),
            /** Data de desligamento; null se o colaborador ainda está ativo. */
            leftDate: parseDateOnly(employment?.leftAt),
        }),
        [employment]
    );
}
