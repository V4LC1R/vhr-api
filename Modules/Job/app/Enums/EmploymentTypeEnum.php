<?php

declare(strict_types=1);

namespace Modules\Job\Enums;

enum EmploymentTypeEnum: string
{
    case CLT        = 'clt';
    case DAYLI      = 'dayli';
    case TEMPORARY  = 'temporary';
    case FREELANCER = 'freelancer';

    public function label(): string
    {
        return match ($this) {
            self::CLT        => 'CLT',
            self::DAYLI      => 'Diarista',
            self::TEMPORARY  => 'Temporário',
            self::FREELANCER => 'Freelancer',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
