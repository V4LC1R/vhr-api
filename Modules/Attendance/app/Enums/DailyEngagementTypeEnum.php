<?php

declare(strict_types=1);

namespace Modules\Attendance\Enums;

enum DailyEngagementTypeEnum: string
{
    /** Dia normal de trabalho — usa as marcações. */
    case WORK = 'work';

    /** Folga — sem jornada esperada, sem débito. */
    case DAY_OFF = 'day_off';

    /** Feriado — sem jornada esperada, sem débito. */
    case HOLIDAY = 'holiday';

    /** Atestado — esperado é abonado (conta como trabalhado). */
    case MEDICAL = 'medical';

    /** Falta não justificada — esperado conta, trabalhado zero (saldo negativo). */
    case ABSENCE = 'absence';

    public function label(): string
    {
        return match ($this) {
            self::WORK    => 'Trabalho',
            self::DAY_OFF => 'Folga',
            self::HOLIDAY => 'Feriado',
            self::MEDICAL => 'Atestado',
            self::ABSENCE => 'Falta',
        };
    }

    /**
     * Se o dia possui jornada esperada (debita do saldo quando não cumprida).
     * Folga e feriado não têm jornada esperada.
     */
    public function hasExpected(): bool
    {
        return match ($this) {
            self::DAY_OFF, self::HOLIDAY => false,
            default                      => true,
        };
    }

    /**
     * Se o esperado é abonado — o dia conta como cumprido sem precisar de marcações
     * (ex.: atestado).
     */
    public function isAbonado(): bool
    {
        return $this === self::MEDICAL;
    }

    /**
     * Se o dia conta presença de marcações (entrada/saída).
     */
    public function usesPunches(): bool
    {
        return $this === self::WORK;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
