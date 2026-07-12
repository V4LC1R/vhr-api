<?php

namespace Modules\Attendance\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Attendance\Enums\DailyEngagementStatusEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Core\Models\User;

class DailyEngagementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('attendance.dailyEngagements.view');
    }

    public function view(User $auth, DailyEngagement $day): bool
    {
        if (! currentCompany()) {
            return false;
        }

        if (! currentCompany()?->can('attendance.dailyEngagements.view')) {
            return false;
        }

        // Rascunho só é visível para quem está draftando.
        if ($day->status === DailyEngagementStatusEnum::DRAFT) {
            return $day->draftedBy === currentCompany()?->id;
        }

        // Contador só enxerga dias aprovados (relatório de horas).
        if ($this->isAccountantOnly() && $day->status !== DailyEngagementStatusEnum::APPROVED) {
            return false;
        }

        // Funcionário comum só enxerga os próprios dias.
        if ($this->isSelfServiceOnly()) {
            return $day->employee?->personId === currentCompany()?->personId;
        }

        return true;
    }

    public function create(User $auth): bool
    {
        return $this->canManage();
    }

    public function upsertException(User $auth, DailyEngagement $day): bool
    {
        return $this->canManage();
    }

    public function submit(User $auth, DailyEngagement $day): bool
    {
        return $this->canManage();
    }

    public function approve(User $auth, DailyEngagement $day): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('attendance.dailyEngagements.approve');
    }

    public function reject(User $auth, DailyEngagement $day): bool
    {
        return $this->approve($auth, $day);
    }

    public function approveBatch(User $auth): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('attendance.dailyEngagements.approve');
    }

    public function rejectBatch(User $auth): bool
    {
        return $this->approveBatch($auth);
    }

    private function canManage(): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->hasRole('owner')
            || currentCompany()?->hasRole('humanResource');
    }

    /**
     * Usuário restrito a auto-atendimento: tem papel employee e nenhum papel de gestão/auditoria.
     */
    private function isSelfServiceOnly(): bool
    {
        return ! currentCompany()?->hasRole('owner')
            && ! currentCompany()?->hasRole('humanResource')
            && ! currentCompany()?->hasRole('accountant');
    }

    private function isAccountantOnly(): bool
    {
        return currentCompany()?->hasRole('accountant')
            && ! currentCompany()?->hasRole('owner')
            && ! currentCompany()?->hasRole('humanResource');
    }
}
