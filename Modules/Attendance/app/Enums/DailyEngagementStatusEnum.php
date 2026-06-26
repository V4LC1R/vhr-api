<?php

declare(strict_types=1);

namespace Modules\Attendance\Enums;

enum DailyEngagementStatusEnum: string
{
    case DRAFT = 'draft';

    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT    => 'Rascunho',
            self::PENDING  => 'Pendente',
            self::APPROVED => 'Aprovado',
            self::REJECTED => 'Rejeitado',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
