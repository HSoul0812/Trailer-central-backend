<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateCarsforsaleEmailIntegration extends Migration
{
    private const CARSFORSALE_PARAMS = [
        'integration_id' => 25,
        'active' => 1
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('integration')
            ->where(self::CARSFORSALE_PARAMS)
            ->update([
                "send_email" => "exports@carsforsale.com"
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration')
            ->where(self::CARSFORSALE_PARAMS)
            ->update([
                "send_email" => "jtokheim@carsforsale.com"
            ]);
    }
}
