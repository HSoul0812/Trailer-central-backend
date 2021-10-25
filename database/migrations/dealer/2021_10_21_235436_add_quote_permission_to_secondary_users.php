<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User\Interfaces\PermissionsInterface;

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
          ->where('feature', PermissionsInterface::CRM)->get();
      
      foreach ($dealerUsers as $dealer_user) {
        if ($dealer_user->permission_level != PermissionsInterface::CANNOT_SEE_PERMISSION) {
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
        Schema::table('dealer_users', function (Blueprint $table) {
            //
        });
    }
}
