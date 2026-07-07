<?php

declare(strict_types=1);

namespace Modules\Core\Enums;

enum TokenPasswordStatusEnum: string
{
    case PENDING = 'pending';

    case USED = 'used';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::USED => 'Utilizado',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
