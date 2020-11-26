<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTypesToDealerIncomingMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE
                      `dealer_incoming_mappings`
                   MODIFY COLUMN
                      `type` enum('manufacturer','category','entity_type','condition','status','color','pull_type','nose_type','construction','fuel_type','brand','manufacturer_brand','dealer_location','fields','default_values');"
        );

        exit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "ALTER TABLE
                      `dealer_incoming_mappings`
                   MODIFY COLUMN
                      `type` enum('manufacturer','category','entity_type','condition','status','color','pull_type','nose_type','construction','fuel_type','brand','manufacturer_brand','dealer_location');"
        );
    }
}
