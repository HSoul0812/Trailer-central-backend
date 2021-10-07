<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateHorseclicksEmailIntegration extends Migration
{
    private const HORSECLICKS_PARAMS = [
        'integration_id' => 1,
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
            ->where(self::HORSECLICKS_PARAMS)
            ->update([
                "send_email" => "natalie.woodford@fridaymediagroup.com"
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
            ->where(self::HORSECLICKS_PARAMS)
            ->update([
                "send_email" => "josef.mattacks@fridaymediagroup.com; drew.macke@fridaymediagroup.com"
            ]);
    }
}
