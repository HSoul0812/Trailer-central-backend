<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUserAccountsToWebsiteConfigDefaultTable extends Migration
{
    private const USER_ACCOUNTS_OPTION = [
        'key' => 'general/user_accounts',
        'private' => 1,
        'type' => 'checkbox',
        'label' => 'Activate/Deactivate user accounts',
        'note' => null,
        'grouping' => 'General',
        'values' => null,
        'default_label' => '',
        'default_value' => '0',
        'sort_order' => 1100,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::USER_ACCOUNTS_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::USER_ACCOUNTS_OPTION['key'])->delete();
    }
}
