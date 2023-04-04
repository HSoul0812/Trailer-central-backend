<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class OptimizeFbme extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conn = DB::connection()->getDoctrineConnection();
        $conn->executeStatement($this->cleanFbAPPErrors());
        $conn->executeStatement($this->dropView());
        $conn->executeStatement($this->createView());
        $conn->executeStatement($this->addIndexFbListings());
        $conn->executeStatement($this->addIndexFbAppErrors());
        $conn->close();
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

    private function cleanFbAPPErrors(): string
    {
        return "DELETE FROM fbapp_errors WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 WEEK);";
    }

    private function dropView(): string
    {
        return "DROP VIEW if EXISTS dealer_fbm_overview;";
    }

    private function createView(): string
    {
        return "
        CREATE VIEW dealer_fbm_overview as
        
        SELECT
            fbm.id AS id,
            d.dealer_id AS dealer_id,
            d.name AS dealer_name,
            IFNULL(fbm.fb_username, 'n/a') AS fb_username,
            IFNULL((SELECT name FROM dealer_location WHERE dealer_location_id = fbm.dealer_location_id), 'ALL') AS location,
            GREATEST(
                COALESCE((SELECT MAX(listed_at) FROM fbme_listings WHERE integration_id = fbm.id), '1000-01-01 00:00:00'),
                COALESCE((SELECT MAX(created_at) FROM fbapp_errors WHERE marketplace_id = fbm.id), '1000-01-01 00:00:00')
            ) AS last_attempt_ts,
            (
            SELECT COUNT(1) > 0
                FROM fbme_listings
                WHERE integration_id = fbm.id
                AND DATE(listed_at) = (
                SELECT GREATEST(
                    COALESCE((SELECT DATE(MAX(listed_at)) FROM fbme_listings WHERE integration_id = fbm.id), '1000-01-01'),
                                    COALESCE((SELECT DATE(MAX(created_at)) FROM fbapp_errors WHERE marketplace_id = fbm.id and error_type <> 'missing-inventory'), '1000-01-01')
                                )
                            )
                        ) AS last_run_status,
            COALESCE((SELECT MAX(created_at) FROM fbapp_errors WHERE marketplace_id = fbm.id), '1000-01-01 00:00:00') AS last_known_error_ts,
            COALESCE((SELECT UC_Delimiter(REPLACE(MAX(error_type), '-', ' '), ' ', TRUE) FROM fbapp_errors WHERE marketplace_id = fbm.id), 'no error') AS last_known_error_code,
            COALESCE((SELECT MAX(error_message) FROM fbapp_errors WHERE marketplace_id = fbm.id), 'no error') AS last_known_error_message,
            COALESCE((SELECT MAX(listed_at) FROM fbme_listings WHERE integration_id = fbm.id), '1000-01-01 00:00:00') AS last_success_ts,
            COALESCE((SELECT GROUP_CONCAT(SKU SEPARATOR ' | ') FROM fbme_listings WHERE integration_id = fbm.id and DATE(listed_at) = (SELECT DATE(MAX(listed_at)) FROM fbme_listings WHERE integration_id = fbm.id)), 'none') AS last_units_posted,
            COALESCE((SELECT GROUP_CONCAT(SKU SEPARATOR ' | ') FROM fbme_listings WHERE integration_id = fbm.id and DATE(listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR))), 'none') AS units_posted_today,
            COALESCE((SELECT GROUP_CONCAT(SKU SEPARATOR ' | ') FROM fbme_listings WHERE integration_id = fbm.id and DATE(listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR))), 'none') AS units_posted_1dayago,
            COALESCE((SELECT GROUP_CONCAT(SKU SEPARATOR ' | ') FROM fbme_listings WHERE integration_id = fbm.id and DATE(listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR))), 'none') AS units_posted_2dayago,
            COALESCE((SELECT GROUP_CONCAT(SKU SEPARATOR ' | ') FROM fbme_listings WHERE integration_id = fbm.id and DATE(listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR))), 'none') AS units_posted_3dayago,
            COALESCE((SELECT GROUP_CONCAT(SKU SEPARATOR ' | ') FROM fbme_listings WHERE integration_id = fbm.id and DATE(listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR))), 'none') AS units_posted_4dayago,
            COALESCE((SELECT GROUP_CONCAT(SKU SEPARATOR ' | ') FROM fbme_listings WHERE integration_id = fbm.id and DATE(listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR))), 'none') AS units_posted_5dayago,
            COALESCE((SELECT MAX(error_message) FROM fbapp_errors WHERE marketplace_id = fbm.id and DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR))), 'no error') AS error_today,
            COALESCE((SELECT MAX(error_message) FROM fbapp_errors WHERE marketplace_id = fbm.id and DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR))), 'no error') AS error_1dayago,
            COALESCE((SELECT MAX(error_message) FROM fbapp_errors WHERE marketplace_id = fbm.id and DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR))), 'no error') AS error_2dayago,
            COALESCE((SELECT MAX(error_message) FROM fbapp_errors WHERE marketplace_id = fbm.id and DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR))), 'no error') AS error_3dayago,
            COALESCE((SELECT MAX(error_message) FROM fbapp_errors WHERE marketplace_id = fbm.id and DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR))), 'no error') AS error_4dayago,
            COALESCE((SELECT MAX(error_message) FROM fbapp_errors WHERE marketplace_id = fbm.id and DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR))), 'no error') AS error_5dayago
        FROM fbapp_marketplace AS fbm
        INNER JOIN dealer AS d ON fbm.dealer_id = d.dealer_id
        GROUP BY d.dealer_id, fbm.id
        ORDER BY last_attempt_ts DESC;";
    }

    private function addIndexFbListings()
    {
        return "ALTER TABLE fbapp_listings ADD INDEX idx_inventory_id(inventory_id), ADD INDEX idx_marketplace_id(marketplace_id), ADD INDEX idx_created_at(created_at), ADD INDEX idx_marketplace_id_created_at(marketplace_id, created_at);";
    }

    private function addIndexFbAppErrors()
    {
        return "ALTER TABLE fbapp_errors ADD INDEX idx_created_at(created_at), ADD INDEX idx_marketplace_id_created_at(marketplace_id, created_at);";
    }




}