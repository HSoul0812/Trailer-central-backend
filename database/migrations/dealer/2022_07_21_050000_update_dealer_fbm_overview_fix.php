<?php

use Illuminate\Database\Migrations\Migration;
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
        $conn = DB::connection()->getDoctrineConnection();
        $conn->executeStatement($this->dropView1());
        $conn->executeStatement($this->dropView2());
        $conn->executeStatement($this->createView1());
        $conn->executeStatement($this->createView2());
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
        $conn->executeStatement($this->dropView2());
        $conn->close();
    }

    private function dropView1(): string
    {
        return "DROP VIEW IF EXISTS dealer_fbm_overview;";
    }

    private function dropView2(): string
    {
        return "DROP VIEW IF EXISTS fbme_listings;";
    }

    private function createView1(): string
    {
        return "
        CREATE VIEW fbme_listings AS 

        SELECT 
            inv.stock AS SKU, 
            fbl.marketplace_id AS integration_id, 
            fbl.created_at AS listed_at, 
            fbl.facebook_id AS facebook_listing_id,
            (
                    SELECT COUNT(1)+1
                    FROM fbapp_listings AS x
                    WHERE x.marketplace_id = fbl.marketplace_id AND fbl.id > x.id
            ) AS rank
        FROM fbapp_listings AS fbl 
        LEFT JOIN inventory AS inv ON fbl.inventory_id=inv.inventory_id
        WHERE fbl.created_at > DATE_SUB(now(), INTERVAL 1 WEEK)";
    }

    private function createView2(): string
    {
        return "
        CREATE VIEW dealer_fbm_overview AS

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
            GROUP_CONCAT(fbme_l1.SKU SEPARATOR ' | ') AS units_posted_today,
            GROUP_CONCAT(fbme_l2.SKU SEPARATOR ' | ') AS units_posted_1dayago,
            GROUP_CONCAT(fbme_l3.SKU SEPARATOR ' | ') AS units_posted_2dayago,
            GROUP_CONCAT(fbme_l4.SKU SEPARATOR ' | ') AS units_posted_3dayago,
            GROUP_CONCAT(fbme_l5.SKU SEPARATOR ' | ') AS units_posted_4dayago,
            GROUP_CONCAT(fbme_l6.SKU SEPARATOR ' | ') AS units_posted_5dayago,
            IFNULL((SELECT CONCAT(step, ': ', error_message) FROM fbapp_errors WHERE marketplace_id=fbm.id AND created_at=CURDATE() ORDER BY id DESC LIMIT 1), 'no error') AS error_today,
            IFNULL((SELECT CONCAT(step, ': ', error_message) FROM fbapp_errors WHERE marketplace_id=fbm.id AND created_at=DATE_SUB(curdate(), INTERVAL 1 DAY) ORDER BY id DESC LIMIT 1), 'no error') AS error_1dayago,
            IFNULL((SELECT CONCAT(step, ': ', error_message) FROM fbapp_errors WHERE marketplace_id=fbm.id AND created_at=DATE_SUB(curdate(), INTERVAL 2 DAY) ORDER BY id DESC LIMIT 1), 'no error') AS error_2dayago,
            IFNULL((SELECT CONCAT(step, ': ', error_message) FROM fbapp_errors WHERE marketplace_id=fbm.id AND created_at=DATE_SUB(curdate(), INTERVAL 3 DAY) ORDER BY id DESC LIMIT 1), 'no error') AS error_3dayago,
            IFNULL((SELECT CONCAT(step, ': ', error_message) FROM fbapp_errors WHERE marketplace_id=fbm.id AND created_at=DATE_SUB(curdate(), INTERVAL 4 DAY) ORDER BY id DESC LIMIT 1), 'no error') AS error_4dayago,
            IFNULL((SELECT CONCAT(step, ': ', error_message) FROM fbapp_errors WHERE marketplace_id=fbm.id AND created_at=DATE_SUB(curdate(), INTERVAL 5 DAY) ORDER BY id DESC LIMIT 1), 'no error') AS error_5dayago

        FROM dealer AS d
        INNER JOIN fbapp_marketplace AS fbm ON fbm.dealer_id=d.dealer_id
        LEFT JOIN fbme_listings AS fbme_l1 ON fbme_l1.integration_id=fbm.id AND DATE(fbme_l1.listed_at) = CURDATE()
        LEFT JOIN fbme_listings AS fbme_l2 ON fbme_l2.integration_id=fbm.id AND DATE(fbme_l2.listed_at) = DATE_SUB(curdate(), INTERVAL 1 DAY)
        LEFT JOIN fbme_listings AS fbme_l3 ON fbme_l3.integration_id=fbm.id AND DATE(fbme_l3.listed_at) = DATE_SUB(curdate(), INTERVAL 2 DAY)
        LEFT JOIN fbme_listings AS fbme_l4 ON fbme_l4.integration_id=fbm.id AND DATE(fbme_l4.listed_at) = DATE_SUB(curdate(), INTERVAL 3 DAY)
        LEFT JOIN fbme_listings AS fbme_l5 ON fbme_l5.integration_id=fbm.id AND DATE(fbme_l5.listed_at) = DATE_SUB(curdate(), INTERVAL 4 DAY)
        LEFT JOIN fbme_listings AS fbme_l6 ON fbme_l6.integration_id=fbm.id AND DATE(fbme_l6.listed_at) = DATE_SUB(curdate(), INTERVAL 5 DAY)

        GROUP BY d.dealer_id, fbm.id

        ORDER BY d.dealer_id, fbm.id DESC";
    }
}
