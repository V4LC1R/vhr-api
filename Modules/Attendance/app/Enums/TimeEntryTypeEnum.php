<?php

declare(strict_types=1);

namespace Modules\Attendance\Enums;

enum TimeEntryTypeEnum: string
{
    case ENTRY = 'entry';

    case EXIT = 'exit';

    public function label(): string
    {
        return match ($this) {
            self::ENTRY => 'Entrada',
            self::EXIT  => 'Saída',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
