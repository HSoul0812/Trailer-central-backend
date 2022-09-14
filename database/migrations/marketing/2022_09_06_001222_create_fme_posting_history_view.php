<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFmePostingHistoryView extends Migration
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
            CREATE VIEW fme_posting_history AS
                    (SELECT fbl.id                                                                               AS record_id,
                            'posting'                                                                            AS type,
                            'success'                                                                            AS status,
                            fbapp_marketplace.dealer_id                                                          AS dealer_id,
                            fbl.marketplace_id                                                                   AS marketplace_id,
                            fbapp_marketplace.fb_username,
                            IF(IFNULL(fbapp_marketplace.dealer_location_id, 0) = 0, 'ALL', dealer_location.name) AS location,
                            inventory.stock                                                                      AS SKU,
                            fbl.inventory_id,
                            fbl.facebook_id,
                            fbl.created_at
                    FROM fbapp_listings as fbl
                              LEFT JOIN fbapp_marketplace ON fbl.marketplace_id = fbapp_marketplace.id
                              LEFT JOIN inventory ON fbl.inventory_id = inventory.inventory_id
                              LEFT JOIN dealer_location ON fbapp_marketplace.dealer_location_id = dealer_location.dealer_location_id)
                UNION ALL
                    (SELECT fbe.id                                                                                                   AS record_id,
                            'error'                                                                                                  AS type,
                            CONVERT(CONCAT(error_type, ': ', error_message) USING utf8)                                              AS status,
                            fbapp_marketplace.dealer_id                                                                              AS dealer_id,
                            fbe.marketplace_id                                                                                       AS marketplace_id,
                            fbapp_marketplace.fb_username,
                            IF(IFNULL(fbapp_marketplace.dealer_location_id, 0) = 0, 'ALL',
                               dealer_location.name)                                                                                 AS location,
                            IF(fbe.inventory_id IS NULL, 'N/A', (SELECT stock FROM inventory WHERE inventory_id = fbe.inventory_id)) AS SKU,
                            IFNULL(fbe.inventory_id, 'N/A')                                                                          AS inventory_id,
                            'N/A'                                                                                                    AS facebook_id,
                            fbe.created_at
                    FROM fbapp_errors as fbe
                              LEFT JOIN fbapp_marketplace ON fbe.marketplace_id = fbapp_marketplace.id
                              LEFT JOIN dealer_location ON fbapp_marketplace.dealer_location_id = dealer_location.dealer_location_id)
                ORDER BY created_at DESC;
		";
    }

    /**
     * Drop the view
     * @return string
     */
    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS fme_posting_history;";
    }
}
