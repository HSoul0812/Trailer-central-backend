<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddQuotePermissionToSecondaryUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $dealerUsers = DB::table('dealer_user_permissions')
          ->select('dealer_user_id', 'permission_level')
          ->where('feature', 'crm')->get();
      
      foreach ($dealerUsers as $dealer_user) {
        if ($dealer_user->permission_level != 'cannot_see') {
          DB::table('dealer_user_permissions')->insert([
              'dealer_user_id' => $dealer_user->dealer_user_id,
              'feature' => 'crm',
              'permission_level' => 'can_see_and_change'
          ]);
        }
      }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_users', function (Blueprint $table) {
            //
        });
    }
}
