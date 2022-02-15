<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddRvasapIntegrationToHiddenIntegrationsTable extends Migration
{
    private const RVASAP_PARAMS = [
        'integration_id' => 79,
        'is_hidden' => 1
    ]; 
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::transaction(function () {
        DB::table('hidden_integrations')->insert(self::RVASAP_PARAMS);
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::transaction(function () {
        DB::table('hidden_integrations')->delete(self::RVASAP_PARAMS['integration_id']);
      });
    }
}
