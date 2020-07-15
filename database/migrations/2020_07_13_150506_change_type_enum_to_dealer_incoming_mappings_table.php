<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTypeEnumToDealerIncomingMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE
                      `dealer_incoming_mappings`
                   MODIFY COLUMN
                      `type` enum('manufacturer','category','entity_type','condition','status','color','pull_type','nose_type','construction','fuel_type','brand','manufacturer_brand') NOT NULL;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE
                      `dealer_incoming_mappings`
                   MODIFY COLUMN
                      `type` enum('manufacturer','category','entity_type','condition','status','color','pull_type','nose_type','construction','fuel_type','brand') NOT NULL;"
        );
    }
}
