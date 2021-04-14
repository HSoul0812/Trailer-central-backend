<?php

namespace App\Traits\Models;

use Illuminate\Support\Collection;

/**
 * Class HasPermissionsEmpty
 * @package App\Traits\Models
 */
trait HasPermissionsStub
{
    /**
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return new Collection([]);
    }

    /**
     * @param string $feature
     * @param string $permissionLevel
     * @return bool
     */
    public function hasPermission(string $feature, string $permissionLevel): bool
    {
        return false;
    }
}
