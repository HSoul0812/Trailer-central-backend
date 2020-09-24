<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeDealerIncomingPendingMappingsType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `dealer_incoming_pending_mapping` CHANGE `type` `type` ENUM('manufacturer','category','entity_type','condition','status','color','pull_type','nose_type','construction','fuel_type','brand','manufacturer_brand','dealer_location') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `dealer_incoming_pending_mapping` CHANGE `type` `type` ENUM('manufacturer','category','entity_type','condition','status','color','pull_type','nose_type','construction','fuel_type','brand') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }
} 
