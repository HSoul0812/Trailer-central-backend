<?php

use App\Models\Integration\Integration;
use Illuminate\Database\Migrations\Migration;

class AddHtwIntegrationOptions extends Migration
{
    /**
     * @const string
     */
    const DOMAIN_TO_UPDATE = 'www.horsetrailerworld.com';

    /**
     * @const array
     */
    const INTEGRATION_SETTINGS = [
        [
            "name" => "username",
            "label" => "Username",
            "description" => "Your HorseTrailerWorld username.",
            "type" => "text",
            "required" => 1
        ],
        [
            "name" => "password",
            "label" => "Password",
            "description" => "Your HorseTrailerWorld password.",
            "type" => "text",
            "required" => 1
        ],
        [
            "name" => "syncing",
            "label" => "Syncing",
            "description" => "How do you wish to sync inventory?",
            "type" => "select",
            "options" => [
                "Delete all inventory not in Trailer Central.",
                "Ignore sold inventory on HTW."
            ]
        ],
        [
            "name" => "statuses",
            "label" => "Statuses",
            "description" => "Status to Import (Available ALWAYS imports)",
            "type" => "select",
            "multiple" => 1,
            "options" => [
                "4" => "Pending Sale",
                "2" => "Sold",
                "3" => "On Order",
                "5" => "Special Order"
            ]
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update HTW Integrations
        Integration::where('domain', self::DOMAIN_TO_UPDATE)
            ->update(['settings' => serialize(self::INTEGRATION_SETTINGS)]);
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
