<?php

declare(strict_types=1);

namespace Modules\Job\Enums;

enum PersonCompanyStatusEnum: string
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
}
