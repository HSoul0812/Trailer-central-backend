<?php

use App\Models\Marketing\Facebook\Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddFmeErrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE `fbapp_errors`
                CHANGE `error_type` `error_type` ENUM('" . implode("','", array_keys(Error::ERROR_TYPES)) . "') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
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
