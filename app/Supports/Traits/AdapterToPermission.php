<?php

namespace App\Supports\Traits;

use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

trait AdapterToPermission
{
    use HasRoles;

    public function can($abilities, $arguments = []): bool
    {
        return $this->hasPermissionTo($abilities, 'web');
    }

    protected function getDefaultGuardName(): string
    {
        return 'web';
    }

    public function getGuardNames(): Collection
    {
        return collect(['web']);
    }
}
