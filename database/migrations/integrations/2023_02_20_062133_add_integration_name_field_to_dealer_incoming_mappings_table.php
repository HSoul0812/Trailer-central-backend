<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntegrationNameFieldToDealerIncomingMappingsTable extends Migration
{
    private const INTEGRATION_NAMES = [
        'pj',
        'utc',
    ];

    private const INTEGRATION_NAME_FIELD = 'integration_name';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_incoming_mappings', function (Blueprint $table) {
            $table->enum(self::INTEGRATION_NAME_FIELD, self::INTEGRATION_NAMES)
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_incoming_mappings', function (Blueprint $table) {
            $table->dropColumn(self::INTEGRATION_NAME_FIELD);
        });
    }
}
