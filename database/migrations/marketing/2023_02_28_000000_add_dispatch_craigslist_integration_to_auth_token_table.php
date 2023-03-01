<?php

use Illuminate\Database\Migrations\Migration;

class AddDispatchCraigslistIntegrationToAuthTokenTable extends Migration
{
    /**
     * @const Facebook Messenger Integration Name
     */
    const CLAPP_INTEGRATION_NAME = 'dispatch_craigslist';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $clId = DB::table('interaction_integration')->insertGetId([
            'name' => self::CLAPP_INTEGRATION_NAME,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);

        DB::table('interaction_integration_permission')->insert([
            'integration_id' => $clId,
            'feature' => 'craigslist_dispatch',
            'permission_level' => 'can_see_and_change',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);

        DB::table('auth_token')->insert([
            'user_id' => $clId,
            'user_type' => 'integration',
            'access_token' => md5($clId . uniqid()),
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
        $cl = DB::table('interaction_integration')->where(['name' => self::CLAPP_INTEGRATION_NAME])->first(['id']);

        // Delete Auth Token
        DB::table('auth_token')->where(['user_type' => 'integration', 'user_id' => $cl->id])->delete();

        // Delete Permission
        DB::table('interaction_integration_permission')->where(['integration_id' => $cl->id])->delete();

        // Delete Integration
        DB::table('interaction_integration')->where(['id' => $cl->id])->delete();
    }
}
