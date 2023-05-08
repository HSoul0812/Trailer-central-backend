<?php

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\User\Interfaces\PermissionsInterface;
use Illuminate\Database\Migrations\Migration;

class AllowTrailertraderCreateUser extends Migration
{
    const INTEGRATION_NAME = 'trailertrader';

    public function up()
    {
        $user = Integration::where([
            'name' => self::INTEGRATION_NAME,
        ])->first();

        if($user) {
            $user->perms()->create([
                'feature' => 'create_user',
                'permission_level' => PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION,
            ]);
        }
    }

    /**
     * @throws Exception
     */
    public function down()
    {
        $user = Integration::where('name', self::INTEGRATION_NAME)->first();

        if ($user === null) {
            return;
        }

        $user->perms()->where([
            'feature' => 'create_user'
        ])->delete();
    }
}
