<?php

use Illuminate\Database\Migrations\Migration;

class AddFacebookMarketplaceDispatchToAuthTokenTable extends Migration
{
    /**
     * @const Facebook Messenger Integration Name
     */
    const FBDISPATCH_INTEGRATION_NAME = 'dispatch_facebook';


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fbId = DB::table('interaction_integration')->insertGetId([
            'name' => self::FBDISPATCH_INTEGRATION_NAME,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);

        DB::table('interaction_integration_permission')->insert([
            'integration_id' => $fbId,
            'feature' => 'fbapp_marketplace',
            'permission_level' => 'can_see_and_change',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);

        DB::table('auth_token')->insert([
            'user_id' => $fbId,
            'user_type' => 'integration',
            'access_token' => md5($fbId . uniqid()),
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
        $fb = DB::table('interaction_integration')->where(['name' => self::FBDISPATCH_INTEGRATION_NAME])->first(['id']);

        // Delete Auth Token
        DB::table('auth_token')->where(['user_type' => 'integration', 'user_id' => $fb->id])->delete();

        // Delete Permission
        DB::table('interaction_integration_permission')->where(['integration_id' => $fb->id])->delete();

        // Delete Integration
        DB::table('interaction_integration')->where(['id' => $fb->id])->delete();
    }
}
