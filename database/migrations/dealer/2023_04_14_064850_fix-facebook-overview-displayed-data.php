<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixFacebookOverviewDisplayedData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
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
     */
    public function down()
    {
        //
    }

    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS dealer_fbm_overview;";
    }


    private function createView(): string
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
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN IFNULL(l.count_units_posted_today, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN IFNULL(l.count_units_posted_1dayago, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN IFNULL(l.count_units_posted_2dayago, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN IFNULL(l.count_units_posted_3dayago, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN IFNULL(l.count_units_posted_4dayago, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN IFNULL(l.count_units_posted_5dayago, 0)
                    ELSE 0
                END
            ) AS last_attempt_posts_remaining,
            (
                CASE
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN IFNULL(l.units_posted_today, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN IFNULL(l.units_posted_1dayago, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN IFNULL(l.units_posted_2dayago, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN IFNULL(l.units_posted_3dayago, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN IFNULL(l.units_posted_4dayago, 0)
                    WHEN DATE(GREATEST(COALESCE(l.max_listed_at, '1000-01-01 00:00:00'), COALESCE(e.latest_error_timestamp, '1000-01-01 00:00:00'))) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN IFNULL(l.units_posted_5dayago, 0)
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
