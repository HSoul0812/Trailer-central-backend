<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User\Interfaces\PermissionsInterface;
use App\Models\User\User;

class AddQuotePermissionToSecondaryUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $users = User::where('is_dms_active', 1)->get();
      
      foreach ($users as $user) {
        foreach ($user->dealerUsers as $dealer_user) {
          DB::table('dealer_user_permissions')->insert([
              'dealer_user_id' => $dealer_user->dealer_user_id,
              'feature' => PermissionsInterface::QUOTES,
              'permission_level' => PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION
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
      DB::table('dealer_user_permissions')
          ->where('feature', PermissionsInterface::QUOTES)
          ->delete();
    }
}
