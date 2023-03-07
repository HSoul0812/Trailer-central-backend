<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DisableHotPotatoAllDealers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('new_crm_user')
          ->where(['enable_hot_potato' => 1])
          ->update(['enable_hot_potato' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
