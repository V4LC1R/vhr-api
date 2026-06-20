<?php

declare(strict_types=1);

namespace Modules\Job\Enums;

enum EmployeeRoleEnum: string
{
    case EMPLOYEE = 'employee';

    case OWNER = 'owner';

    case HUMAN_RESOURCE = 'humanResource';

    case ACCOUNTANT = 'accountant';

    public function label(): string
    {
        return match ($this) {
            self::EMPLOYEE => 'Funcionário',
            self::OWNER => 'Proprietário',
            self::HUMAN_RESOURCE => 'Recursos Humanos',
            self::ACCOUNTANT => 'Contador',
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
