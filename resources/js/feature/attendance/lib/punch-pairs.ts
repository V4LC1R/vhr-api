import { TimeEntry } from "@/types/timeEntry/types";

/**
 * Par entrada → saída. `entry` ou `exit` nulos indicam sequência quebrada
 * (entrada sem saída, saída órfã ou duas entradas seguidas).
 */
export interface PunchPair {
    entry: TimeEntry | null;
    exit: TimeEntry | null;
}

export interface PunchPairsResult {
    pairs: PunchPair[];
    hasAnomaly: boolean;
}

/**
 * Monta os pares entrada/saída em ordem cronológica e sinaliza inconsistências
 * de sequência — é o que o aprovador precisa enxergar de cara.
 */
export function buildPunchPairs(punches: TimeEntry[]): PunchPairsResult {
    const sorted = [...punches].sort((a, b) => a.punchedAt.localeCompare(b.punchedAt));

    const pairs: PunchPair[] = [];
    let hasAnomaly = false;
    let current: PunchPair | null = null;

    for (const punch of sorted) {
        if (punch.type === "entry") {
            if (current) {
                // Duas entradas seguidas — fecha o par quebrado e recomeça.
                hasAnomaly = true;
                pairs.push(current);
            }
            current = { entry: punch, exit: null };
        } else if (current) {
            current.exit = punch;
            pairs.push(current);
            current = null;
        } else {
            // Saída sem entrada aberta.
            hasAnomaly = true;
            pairs.push({ entry: null, exit: punch });
        }
    }

    if (current) {
        // Entrada que nunca fechou.
        hasAnomaly = true;
        pairs.push(current);
    }

    return { pairs, hasAnomaly };
}

export function isBrokenPair(pair: PunchPair): boolean {
    return !pair.entry || !pair.exit;
}
