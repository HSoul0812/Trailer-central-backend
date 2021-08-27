<?php

namespace App\Traits\Models;

use Illuminate\Support\Collection;
use App\Models\User\DealerUserPermission;

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
     * Returns permissions allowed for a given user
     *
     * @return Collection
     */
    public function getPermissionsAllowed(): Collection
    {
        // Get all permissions
        return DealerUserPermission::where('id', '>', 0)
                                ->groupBy('feature')
                                ->get();
    }

    /**
     * @param string $feature
     * @param string $permissionLevel
     * @return bool
     */
    public function hasPermission(string $feature, string $permissionLevel): bool
    {
        return true;
    }
}
