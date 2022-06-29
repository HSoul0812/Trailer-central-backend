<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRacingJunkSendEmail extends Migration
{
    private const RACING_JUNK_PARAMS = [
        'integration_id' => 7,
        'code' => 'racingjunk',
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
            ->where(self::RACING_JUNK_PARAMS)
            ->update([
                "send_email" => "mkittle@motorheadmedia.com"
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
            ->where(self::RACING_JUNK_PARAMS)
            ->update([
                "send_email" => "amatys@motorheadmedia.com"
            ]);
    }
}
