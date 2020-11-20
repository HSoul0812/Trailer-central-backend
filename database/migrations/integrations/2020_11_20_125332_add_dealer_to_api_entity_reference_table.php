<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDealerToApiEntityReferenceTable extends Migration
{
    private const DEALER_PARAMS = [
        'entity_id' => 2211,
        'reference_id' => '3-3054-C',
        'entity_type' => 'dealer',
        'api_key' => 'utc'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('api_entity_reference')->insert(self::DEALER_PARAMS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integrations')->where(self::DEALER_PARAMS)->delete();
    }
}
