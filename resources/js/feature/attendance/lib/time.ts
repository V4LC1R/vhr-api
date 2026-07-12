import { format } from "date-fns";

/** Minutos → "8h05". */
export function formatMinutes(minutes: number): string {
    const abs = Math.abs(minutes);
    const hours = Math.floor(abs / 60);
    const mins = abs % 60;
    return `${hours}h${String(mins).padStart(2, "0")}`;
}

/** Saldo com sinal: 30 → "+0h30", -75 → "-1h15", 0 → "0h00". */
export function formatBalance(minutes: number): string {
    const prefix = minutes > 0 ? "+" : minutes < 0 ? "-" : "";
    return `${prefix}${formatMinutes(minutes)}`;
}

/** punchedAt (ISO em UTC) → "HH:mm" no fuso local do navegador. */
export function punchTimeLocal(punchedAt: string): string {
    return format(new Date(punchedAt), "HH:mm");
}

/**
 * Data do dia (YYYY-MM-DD) + hora local (HH:mm) → ISO com offset do fuso
 * (ex.: 2026-06-10T08:00:00-03:00). O back converte pra UTC ao gravar.
 */
export function toPunchedAtISO(date: string, time: string): string {
    return format(new Date(`${date}T${time}:00`), "yyyy-MM-dd'T'HH:mm:ssXXX");
}
