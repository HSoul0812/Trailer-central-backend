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

        $conn->executeStatement($this->dropView1());
        $conn->executeStatement($this->dropView2());
        $conn->executeStatement($this->dropView3());
        $conn->executeStatement($this->dropView4());
        $conn->executeStatement($this->dropUCFunctions());

        $conn->executeStatement($this->createView1());
        $conn->executeStatement($this->createView2());
        $conn->executeStatement($this->createView3());

        $conn->close();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $conn = DB::connection()->getDoctrineConnection();
        $conn->executeStatement($this->dropView1());
        $conn->executeStatement($this->dropView3());
        $conn->executeStatement($this->dropView4());
        $conn->close();
    }

    private function cleanFbAPPErrors(): string
    {
        return "DELETE FROM fbapp_errors WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 WEEK);";
    }


    private function dropView1(): string
    {
        return "DROP VIEW IF EXISTS dealer_fbm_overview;";
    }

    private function dropView2(): string
    {
        return "DROP VIEW IF EXISTS fbme_listings;";
    }

    private function dropView3(): string
    {
        return "DROP VIEW IF EXISTS fbmi_listings_aggregated;";
    }

    private function dropView4(): string
    {
        return "DROP VIEW IF EXISTS fbmi_errors_aggregated;";
    }

    private function dropUCFunctions(): string
    {
        return "DROP FUNCTION IF EXISTS UC_First;
                DROP FUNCTION IF EXISTS UC_Delimiter;";
    }

    private function createView1(): string
    {
        return "
        CREATE VIEW fbmi_listings_aggregated AS
        SELECT
            marketplace_id AS integration_id,
            MAX(fbapp_listings.created_at) AS max_listed_at,
            GROUP_CONCAT(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN stock ELSE NULL END SEPARATOR ' | ') AS units_posted_today,
            SUM(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN 1 ELSE 0 END) AS count_units_posted_today,
            GROUP_CONCAT(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN stock ELSE NULL END SEPARATOR ' | ') AS units_posted_1dayago,
            SUM(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN 1 ELSE 0 END) AS count_units_posted_1dayago,
            GROUP_CONCAT(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN stock ELSE NULL END SEPARATOR ' | ') AS units_posted_2dayago,
            SUM(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN 1 ELSE 0 END) AS count_units_posted_2dayago,
            GROUP_CONCAT(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN stock ELSE NULL END SEPARATOR ' | ') AS units_posted_3dayago,
            SUM(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN 1 ELSE 0 END) AS count_units_posted_3dayago,
            GROUP_CONCAT(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN stock ELSE NULL END SEPARATOR ' | ') AS units_posted_4dayago,
            SUM(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN 1 ELSE 0 END) AS count_units_posted_4dayago,
            GROUP_CONCAT(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN stock ELSE NULL END SEPARATOR ' | ') AS units_posted_5dayago,
            SUM(CASE WHEN DATE(fbapp_listings.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN 1 ELSE 0 END) AS count_units_posted_5dayago
        FROM
            fbapp_listings
        LEFT JOIN inventory 
            ON fbapp_listings.inventory_id=inventory.inventory_id
        GROUP BY
            marketplace_id;";
    }

    private function createView2(): string
    {
        return "
        CREATE VIEW fbmi_errors_aggregated AS
        SELECT
            e1.marketplace_id AS integration_id,
            MAX(e1.updated_at) AS latest_error_timestamp,
            (SELECT e2.error_type FROM fbapp_errors e2 WHERE e2.marketplace_id = e1.marketplace_id AND e2.updated_at = MAX(e1.updated_at)) AS latest_error_type,
            (SELECT e3.error_message FROM fbapp_errors e3 WHERE e3.marketplace_id = e1.marketplace_id AND e3.updated_at = MAX(e1.updated_at)) AS latest_error_message,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_today,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_1dayago,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_2dayago,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_3dayago,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_4dayago,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_5dayago,
            
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_today,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_1dayago,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_2dayago,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_3dayago,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_4dayago,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_5dayago
        FROM
            fbapp_errors e1
        GROUP BY
            e1.marketplace_id;";
    }

    private function createView3(): string
    {
        return "
        CREATE VIEW dealer_fbm_overview AS
        SELECT
            fbm.id AS id,
            CONCAT(d.dealer_id, ' - ', d.name, '( ',IFNULL(dl.name, 'ALL'),' )') AS dealer,
            IFNULL(fbm.fb_username, 'n/a') AS fb_username,
            IFNULL(fbm.posts_per_day, 3) AS posts_per_day,
            GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00')) AS last_attempt_ts,
            
            IFNULL(fbm.posts_per_day, 3) - (
                CASE
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN IFNULL(l.count_units_posted_today, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN IFNULL(l.count_units_posted_1dayago, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN IFNULL(l.count_units_posted_2dayago, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN IFNULL(l.count_units_posted_3dayago, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN IFNULL(l.count_units_posted_4dayago, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN IFNULL(l.count_units_posted_5dayago, 0)
                    ELSE 0
                END
            ) AS last_attempt_posts_remaining,
            (
                CASE
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN IFNULL(l.units_posted_today, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN IFNULL(l.units_posted_1dayago, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN IFNULL(l.units_posted_2dayago, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN IFNULL(l.units_posted_3dayago, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN IFNULL(l.units_posted_4dayago, 0)
                    WHEN DATE(last_attempt_ts) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN IFNULL(l.units_posted_5dayago, 0)
                    ELSE 0
                END
            ) AS last_attempt_posts,
            
            COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00') AS last_known_error_ts,
            COALESCE(e.latest_error_type, 'no error') AS last_known_error_type,
            COALESCE(e.latest_error_message, 'no error') AS last_known_error_message,
            
            COALESCE(l.max_listed_at, '1000-01-01 00:00:00') AS last_success_ts,
            
            l.units_posted_today AS units_posted_today,
            l.units_posted_1dayago AS units_posted_1dayago,
            l.units_posted_2dayago AS units_posted_2dayago,
            l.units_posted_3dayago AS units_posted_3dayago,
            l.units_posted_4dayago AS units_posted_4dayago,
            l.units_posted_5dayago AS units_posted_5dayago,
            
            IFNULL(l.count_units_posted_today, 0) AS count_units_posted_today,
            IFNULL(l.count_units_posted_1dayago, 0) AS count_units_posted_1dayago,
            IFNULL(l.count_units_posted_2dayago, 0) AS count_units_posted_2dayago,
            IFNULL(l.count_units_posted_3dayago, 0) AS count_units_posted_3dayago,
            IFNULL(l.count_units_posted_4dayago, 0) AS count_units_posted_4dayago,
            IFNULL(l.count_units_posted_5dayago, 0) AS count_units_posted_5dayago,
        
            e.latest_error_message_today AS error_today,
            e.latest_error_message_1dayago AS error_1dayago,
            e.latest_error_message_2dayago AS error_2dayago,
            e.latest_error_message_3dayago AS error_3dayago,
            e.latest_error_message_4dayago AS error_4dayago,
            e.latest_error_message_5dayago AS error_5dayago
        FROM
            fbapp_marketplace AS fbm
            INNER JOIN dealer AS d ON fbm.dealer_id = d.dealer_id
            LEFT JOIN dealer_location AS dl ON fbm.dealer_location_id = dl.dealer_location_id
            LEFT JOIN fbmi_listings_aggregated AS l ON fbm.id = l.integration_id
            LEFT JOIN fbmi_errors_aggregated AS e ON fbm.id = e.integration_id
        GROUP BY
            d.dealer_id,
            fbm.id,
            d.name,
            fb_username,
            dl.name
        ORDER BY
            last_attempt_ts DESC;   ";
    }

}