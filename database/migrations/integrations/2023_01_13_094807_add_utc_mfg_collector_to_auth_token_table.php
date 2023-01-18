<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUtcMfgCollectorToAuthTokenTable extends Migration
{
    private const INTERACTION_INTEGRATION_TABLE = 'interaction_integration';
    private const AUTH_TOKEN_TABLE = 'auth_token';
    private const INTEGRATION_NAME = 'utc';
    private const USER_TYPE = 'integration';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table(self::INTERACTION_INTEGRATION_TABLE)->insert([
            'name' => self::INTEGRATION_NAME,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);

        $userId = DB::getPdo()->lastInsertId();

        DB::table(self::AUTH_TOKEN_TABLE)->insert([
            'user_id' => $userId,
            'user_type' => self::USER_TYPE,
            'access_token' => md5($userId.uniqid()),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $utc = DB::table(self::INTERACTION_INTEGRATION_TABLE)->where(['name' => self::INTEGRATION_NAME])->first(['id']);

        DB::table(self::INTERACTION_INTEGRATION_TABLE)->where(['id' => $utc->id])->delete();
        DB::table(self::AUTH_TOKEN_TABLE)->where(['user_id' => $utc->id, 'user_type' => self::USER_TYPE])->delete();
    }
}
