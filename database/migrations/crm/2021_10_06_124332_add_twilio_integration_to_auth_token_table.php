<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTwilioIntegrationToAuthTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $twilio = DB::table('interaction_integration')->where(['name' => 'twilio',])->first(['id']);

        DB::table('auth_token')->insert([
            'user_id' => $twilio->id,
            'user_type' => 'integration',
            'access_token' => md5($twilio->id.uniqid()),
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
        $twilio = DB::table('interaction_integration')->where(['name' => 'twilio',])->first(['id']);

        DB::table('auth_token')->where([
            'user_id' => $twilio->id,
            'user_type' => 'integration',
        ])->delete();
    }
}
