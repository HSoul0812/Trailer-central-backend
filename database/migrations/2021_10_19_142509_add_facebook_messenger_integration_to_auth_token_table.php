<?php

use Illuminate\Database\Migrations\Migration;

class AddFacebookMessengerIntegrationToAuthTokenTable extends Migration
{
    /**
     * @const Facebook Messenger Integration Name
     */
    const FBCHAT_INTEGRATION_NAME = 'facebook_messenger';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fb = DB::table('interaction_integration')->insert([
            'name' => self::FBCHAT_INTEGRATION_NAME,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);

        DB::table('interaction_integration_permission')->insert([
            'integration_id' => $fb->id,
            'feature' => 'fbapp_messages',
            'permission_level' => 'can_see_and_change',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);

        DB::table('auth_token')->insert([
            'user_id' => $fb->id,
            'user_type' => 'integration',
            'access_token' => md5($fb->id . uniqid()),
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
        $fb = DB::table('interaction_integration')->where(['name' => self::FBCHAT_INTEGRATION_NAME])->first(['id']);

        // Delete Auth Token
        DB::table('auth_token')->delete(['user_id' => $fb->id]);

        // Delete Permission
        DB::table('interaction_integration_permission')->delete(['integration_id' => $fb->id]);

        // Delete Integration
        DB::table('interaction_integration')->delete(['id' => $fb->id]);
    }
}
