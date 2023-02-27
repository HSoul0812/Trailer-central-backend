<?php

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\User\Interfaces\PermissionsInterface;
use Illuminate\Database\Migrations\Migration;

class AddTrailerTraderIntegration extends Migration
{
    const INTEGRATION_NAME = 'trailertrader';

    public function up()
    {
        $user = Integration::create([
            'name' => self::INTEGRATION_NAME,
        ]);

        $user->perms()->create([
            'feature' => 'get_dealers_by_name',
            'permission_level' => PermissionsInterface::CAN_SEE_PERMISSION,
        ]);

        $user->authToken()->create([
            'user_type' => AuthToken::USER_TYPE_INTEGRATION,
            'access_token' => Str::random(AuthToken::INTEGRATION_ACCESS_TOKEN_LENGTH),
        ]);
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

        $user->authToken()->delete();
        $user->perms()->delete();
        $user->delete();
    }
}
