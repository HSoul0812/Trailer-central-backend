<?php

use App\Domains\Permission\Actions\PopulateMissingDealerUserPermissionsAction;
use App\Models\User\Interfaces\PermissionsInterface;
use Illuminate\Database\Migrations\Migration;

class PopulateMissingQuotesPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        resolve(PopulateMissingDealerUserPermissionsAction::class)
            ->setFeature(PermissionsInterface::QUOTES)
            ->setDefaultPermissionLevel(PermissionsInterface::CANNOT_SEE_PERMISSION)
            ->execute();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing to do here
    }
}
