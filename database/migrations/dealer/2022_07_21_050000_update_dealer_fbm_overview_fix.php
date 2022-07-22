<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateDealerFbmOverviewFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->dropView1());
        DB::statement($this->dropView2());
        DB::statement($this->createView1());
        DB::statement($this->createView2());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView1());
        DB::statement($this->dropView2());
    }

    private function dropView1(): string
    {
        return "DROP VIEW IF EXISTS `trailercentral`.`dealer_fbm_overview`;";
    }

    private function dropView2(): string
    {
        return "DROP VIEW IF EXISTS `trailercentral`.`fbme_listings`;";
    }

    private function createView1(): string
    {
        return "
        CREATE VIEW `trailercentral`.`fbme_listings` AS 

        SELECT i.stock, fbl.marketplace_id, fbl.facebook_id,
        (
                SELECT COUNT(1)+1
                FROM fbapp_listings AS x
                WHERE 
                    x.marketplace_id = fbl.marketplace_id 
                    and x.id > fbl.id
        ) AS rank
        FROM fbapp_listings AS fbl 
        LEFT JOIN inventory AS i ON fbl.inventory_id=i.inventory_id
        ORDER BY fbl.id DESC;";
    }

    private function createView2(): string
    {
        return "
        CREATE VIEW `trailercentral`.`dealer_fbm_overview` AS

        SELECT 
            d.dealer_id AS id,
            fbm.id AS marketplace_id,
            d.name AS name,
            IFNULL(fbm.fb_username, 'n/a') AS fb_username,
            IF(ISNULL(fbm.dealer_location_id),'ALL',(SELECT name FROM dealer_location WHERE dealer_location_id = fbm.dealer_location_id )) as location,
            GREATEST(
                (IFNULL((SELECT created_at FROM fbapp_listings WHERE marketplace_id = fbm.id ORDER BY id DESC LIMIT 1),'1000-01-01 00:00:00')),
                (IFNULL((SELECT created_at FROM fbapp_errors WHERE marketplace_id = fbm.id ORDER BY id DESC LIMIT 1),'1000-01-01 00:00:00'))
            ) AS last_run_ts,
            (
                (IFNULL((SELECT created_at FROM fbapp_listings WHERE marketplace_id = fbm.id ORDER BY id DESC LIMIT 1),'1000-01-01 00:00:00')) >
                (IFNULL((SELECT created_at FROM fbapp_errors WHERE marketplace_id = fbm.id ORDER BY id DESC LIMIT 1),'1000-01-01 00:00:00'))
            ) AS last_run_status,
            GROUP_CONCAT(stock SEPARATOR ' | ') AS units_posted,
            IFNULL((SELECT CONCAT(step, ' - ', error_message) FROM fbapp_errors WHERE marketplace_id=fbm.id ORDER BY id DESC LIMIT 1), 'no error') AS last_error

        FROM dealer AS d
        INNER JOIN fbapp_marketplace AS fbm ON fbm.dealer_id=d.dealer_id
        LEFT JOIN fbme_listings ON fbme_listings.marketplace_id=fbm.id AND fbme_listings.rank<7

        GROUP BY d.dealer_id, d.name, fbm.fb_username

        ORDER BY d.dealer_id DESC";
    }
}
