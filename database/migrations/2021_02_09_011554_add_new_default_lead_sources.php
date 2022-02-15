<?php

use App\Models\CRM\Leads\LeadSource;
use Illuminate\Database\Migrations\Migration;

class AddNewDefaultLeadSources extends Migration
{
    /**
     * @const array
     */
    const SOURCE_TRAILER_TRADER = [
        "user_id" => "0",
        "source_name" => "TrailerTrader"
    ];

    /**
     * @const array
     */
    const SOURCE_HORSE_TRAILER_NET = [
        "user_id" => "0",
        "source_name" => "HorseTrailerNet"
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert Lead Sources
        LeadSource::create(self::SOURCE_TRAILER_TRADER);
        LeadSource::create(self::SOURCE_HORSE_TRAILER_NET);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
