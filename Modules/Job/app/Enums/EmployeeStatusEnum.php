<?php

declare(strict_types=1);

namespace Modules\Job\Enums;

enum EmployeeStatusEnum: string
{
    case HIRED = 'hired';

    case EXPERIENCE = 'experience';

    case OUT = 'out';

    public function label(): string
    {
        return match ($this) {
            self::HIRED => 'Contratado',
            self::EXPERIENCE => 'Experiência',
            self::OUT => 'Desligado',
        };
    }

    public static function values(): array
    {
        return array_column(
            self::cases(),
            'value'
        );
    }
}
