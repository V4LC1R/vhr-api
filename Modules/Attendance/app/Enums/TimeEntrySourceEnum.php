<?php

declare(strict_types=1);

namespace Modules\Attendance\Enums;

enum TimeEntrySourceEnum: string
{
    /** Lançamento manual (digitalização da planilha). */
    case MANUAL = 'manual';

    /** Marcação vinda de ponto eletrônico (futuro). */
    case DEVICE = 'device';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::DEVICE => 'Dispositivo',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
