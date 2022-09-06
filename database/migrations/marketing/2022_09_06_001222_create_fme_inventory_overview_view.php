<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFmeInventoryOverviewView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function up()
    {
        $conn = DB::connection()->getDoctrineConnection();

        $conn->executeStatement($this->dropView());
        $conn->executeStatement($this->createView());

        $conn->close();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function down()
    {
        $conn = DB::connection()->getDoctrineConnection();

        $conn->executeStatement($this->dropView());

        $conn->close();
    }

    /**
     * Create an overview for facebook inventory (listings and errors) used by FME
     *
     * @return void
     */
    private function createView(): string
    {
        return "
            CREATE VIEW fme_inventory_overview AS
                (SELECT CONVERT(IF(fml.id IS NOT NULL, fml.id, inv.inventory_id) USING utf8)  AS type_id,
                    IF(fml.id IS NOT NULL, 'listing', 'inventory')                            AS type,
                    IF(fml.created_at IS NOT NULL, fml.created_at, inv.created_at)            AS created_at,
                    fbm.id                                                                    AS marketplace_id,
                    inv.inventory_id                                                          AS inventory_id,
                    fbm.dealer_location_id,
                    IF((fbm.dealer_location_id IS NOT NULL AND fbm.dealer_location_id!=0), IFNULL((SELECT name FROM dealer_location WHERE dealer_location.dealer_location_id = fbm.dealer_location_id), 'MISSING LOCATION'), 'ALL') AS location,
                    fbm.dealer_id,
                    fbm.fb_username,
                    inv.title,
                    CONVERT(CONCAT(fml.year,  ' - ', fml.make,  ' - ', fml.model) USING utf8) AS name
                FROM fbapp_marketplace AS fbm
                      LEFT JOIN inventory AS inv ON inv.dealer_id = fbm.dealer_id
                      LEFT OUTER JOIN fbapp_listings AS fml ON fbm.id = fml.marketplace_id
                AND inv.inventory_id = fml.inventory_id)
            UNION ALL
                (SELECT CONVERT(IF(fme.id IS NOT NULL, fme.id, inv.inventory_id) USING utf8)       AS type_id,
                    IF(fme.id IS NOT NULL, 'error', 'inventory')                                   AS type,
                    IF(fme.created_at IS NOT NULL, fme.created_at, inv.created_at)                 AS created_at,
                    fbm.id                                                                         AS marketplace_id,
                    inv.inventory_id                                                               AS inventory_id,
                    fbm.dealer_location_id,
                    IF((fbm.dealer_location_id IS NOT NULL AND fbm.dealer_location_id!=0), IFNULL((SELECT name FROM dealer_location WHERE dealer_location.dealer_location_id = fbm.dealer_location_id), 'MISSING LOCATION'), 'ALL') AS location,
                    fbm.dealer_id,
                    fbm.fb_username,
                    inv.title,
                    CONVERT(CONCAT(fme.action,  ' - ', fme.step,  ' - ', fme.error_type) USING utf8) AS name
                FROM fbapp_marketplace AS fbm
                    LEFT JOIN inventory AS inv ON inv.dealer_id = fbm.dealer_id
                    LEFT OUTER JOIN fbapp_errors AS fme ON fbm.id = fme.marketplace_id
                AND inv.inventory_id = fme.inventory_id)
            ORDER BY created_at DESC;
		";
    }

    /**
     * Drop the view
     * @return string
     */
    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS fme_inventory_overview;";
    }
}
