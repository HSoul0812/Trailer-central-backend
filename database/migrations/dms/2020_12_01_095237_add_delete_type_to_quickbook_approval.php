<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDeleteTypeToQuickbookApproval extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `quickbook_approval` CHANGE `action_type` `action_type` ENUM('add','update','delete') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'add';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `quickbook_approval` CHANGE `action_type` `action_type` ENUM('add','update') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'add';");
    }
}
