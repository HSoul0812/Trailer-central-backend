<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFfFieldsToDealerIncomingMappingsTable extends Migration
{
    private const DEALER_INCOMING_MAPPINGS_TABLE = 'dealer_incoming_mappings';

    private const PJ_DEALER_ID = '-10';



    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table(self::DEALER_INCOMING_MAPPINGS_TABLE)->insert([
            'name' => self::PJ_DEALER_ID,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);
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
