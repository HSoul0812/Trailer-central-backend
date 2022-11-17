<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Marketing\Facebook\Marketplace;

class Add2faCodeSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE `fbapp_marketplace`
                CHANGE `tfa_type` `tfa_type` ENUM('" . implode("','", array_keys(Marketplace::TFA_TYPES)) . "') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
