<?php

declare(strict_types=1);

namespace Modules\Attendance\Support;

use Modules\Attendance\Enums\DailyEngagementTypeEnum;

/**
 * Regra de valor da diária para vínculos `dayli`.
 *
 * IMPLEMENTAÇÃO PARCIAL: a regra real (presença x horas trabalhadas x meia-diária
 * por limiar) ainda depende de confirmação do cliente final. Toda a lógica de
 * "quanto vale a diária do dia" vive AQUI — é o único ponto a trocar quando a
 * regra definitiva for definida. O dado cru necessário a qualquer regra
 * (worked_minutes + type/presença) já é persistido em cada dia.
 */
class DiariaRule
{
    /**
     * Regra PROVISÓRIA — por presença: trabalhou no dia => 1 diária; senão 0.
     *
     * // PROVISÓRIO — confirmar com cliente (presença x horas x meia-diária)
     */
    public static function provisional(
        DailyEngagementTypeEnum $type,
        int $workedMinutes
    ): float {
        if ($type === DailyEngagementTypeEnum::WORK && $workedMinutes > 0) {
            return 1.0;
        }

        return 0.0;
    }
}
