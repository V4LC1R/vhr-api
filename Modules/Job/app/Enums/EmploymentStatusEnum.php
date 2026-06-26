<?php

declare(strict_types=1);

namespace Modules\Job\Enums;

enum EmploymentStatusEnum: string
{
    case HIRED = 'hired';

    case EXPERIENCE = 'experience';

    case LEFT = 'left';

    public function label(): string
    {
        return match ($this) {
            self::HIRED      => 'Contratado',
            self::EXPERIENCE => 'Experiência',
            self::LEFT       => 'Desligado',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
