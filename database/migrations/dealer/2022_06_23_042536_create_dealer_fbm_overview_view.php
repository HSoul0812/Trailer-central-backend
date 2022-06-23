<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDealerFbmOverviewView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

    private function dropView() : string
    {
        return "DROP VIEW IF EXISTS `dealer_fbm_overview`;";
    }

    private function createView() : string
    {
        return "

        CREATE VIEW `trailercentral`.`dealer_fbm_overview` AS

        SELECT 
            d.dealer_id AS id,
            d.name AS name,
            IFNULL(fbm.fb_username, 'n/a') AS fb_username,
            IF(ISNULL(fbm.dealer_location_id),'ALL',(SELECT name FROM dealer_location WHERE dealer_location_id = fbm.dealer_location_id )) as location,
            IFNULL((SELECT MAX(date_posted) FROM fbapp_inventory WHERE dealer_id=d.dealer_id), 'never') AS last_run_ts,
            'n/a' AS last_run_status,
            IFNULL(GROUP_CONCAT(i.stock SEPARATOR '\n'), 'none') AS units_posted,
            IFNULL((SELECT CONCAT(`action`, ' - ', step, ' - ', error_message) FROM fbapp_errors WHERE marketplace_id=fbm.id ORDER BY id DESC LIMIT 1), 'no error') AS last_error

        FROM dealer AS d
        LEFT JOIN fbapp_marketplace AS fbm ON fbm.dealer_id=d.dealer_id
        LEFT JOIN fbapp_listings AS fbl ON fbm.id=fbl.marketplace_id
        LEFT JOIN inventory AS i ON fbl.inventory_id=i.inventory_id

        GROUP BY d.dealer_id, d.name, fbm.fb_username

        ";
    }
}
