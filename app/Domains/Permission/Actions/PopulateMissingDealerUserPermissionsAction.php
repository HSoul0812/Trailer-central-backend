<?php

namespace App\Domains\Permission\Actions;

use App\Models\User\DealerUser;
use App\Models\User\DealerUserPermission;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class PopulateMissingDealerUserPermissionsAction
{
    private $feature;

    private $defaultPermissionLevel;

    public function execute(): void
    {
        $missingPermissions = $this->getMissingPermissions();

        DB::table(DealerUserPermission::getTableName())->insert($missingPermissions->toArray());
    }

    /**
     * Get the missing permissions
     *
     * @return Collection
     */
    private function getMissingPermissions(): Collection
    {
        return DB::query()
            ->from(DealerUser::getTableName())
            ->whereNotExists(function (Builder $builder) {
                $builder
                    ->from(DealerUserPermission::getTableName())
                    ->whereColumn('dealer_user_permissions.dealer_user_id', '=', 'dealer_users.dealer_user_id')
                    ->where('feature', $this->feature);
            })
            ->pluck('dealer_user_id')
            ->map(function (int $dealerUserId) {
                return [
                    'dealer_user_id' => $dealerUserId,
                    'feature' => $this->feature,
                    'permission_level' => $this->defaultPermissionLevel,
                ];
            });
    }

    /**
     * @param mixed $feature
     * @return PopulateMissingDealerUserPermissionsAction
     */
    public function setFeature($feature): PopulateMissingDealerUserPermissionsAction
    {
        $this->feature = $feature;

        return $this;
    }

    /**
     * @param mixed $defaultPermissionLevel
     * @return PopulateMissingDealerUserPermissionsAction
     */
    public function setDefaultPermissionLevel($defaultPermissionLevel): PopulateMissingDealerUserPermissionsAction
    {
        $this->defaultPermissionLevel = $defaultPermissionLevel;

        return $this;
    }
}
