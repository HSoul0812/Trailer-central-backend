<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterDealerFbmOverview extends Migration
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
        $conn->executeStatement($this->dropUCFunctions());
        $this->createFunctions();
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
        $conn->executeStatement($this->dropUCFunctions());
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

    private function dropUCFunctions(): string
    {
        return "DROP FUNCTION IF EXISTS UC_First;
                DROP FUNCTION IF EXISTS UC_Delimiter;";
    }

    private function createFunctions(): void
    {
        $functionUCFirst = <<<SQL
CREATE FUNCTION UC_First(oldWord VARCHAR(255)) RETURNS VARCHAR(255)
  RETURN CONCAT(UCASE(SUBSTRING(oldWord, 1, 1)),SUBSTRING(oldWord, 2));
SQL;

        DB::unprepared($functionUCFirst);
        $functionUCDelimiter = <<<SQL

CREATE FUNCTION UC_Delimiter(oldName VARCHAR(255), delim VARCHAR(1), trimSpaces BOOL) RETURNS VARCHAR(255)
BEGIN
  SET @oldString := oldName;
  SET @newString := "";

  tokenLoop: LOOP
    IF trimSpaces THEN SET @oldString := TRIM(BOTH " " FROM @oldString); END IF;

    SET @splitPoint := LOCATE(delim, @oldString);

    IF @splitPoint = 0 THEN
      SET @newString := CONCAT(@newString, UC_FIRST(@oldString));
      LEAVE tokenLoop;
    END IF;

    SET @newString := CONCAT(@newString, UC_FIRST(SUBSTRING(@oldString, 1, @splitPoint)));
    SET @oldString := SUBSTRING(@oldString, @splitPoint+1);
  END LOOP tokenLoop;

  RETURN @newString;
END
SQL;

        DB::unprepared($functionUCDelimiter);
    }

    private function createView1(): string
    {
        return "
        CREATE VIEW fbme_listings AS

        SELECT
            inv.stock AS SKU,
            fbl.marketplace_id AS integration_id,
            fbl.created_at AS listed_at,
            fbl.facebook_id AS facebook_listing_id
        FROM fbapp_listings AS fbl
        LEFT JOIN inventory AS inv ON fbl.inventory_id=inv.inventory_id";
    }

    private function createView2(): string
    {
        return "
        CREATE VIEW dealer_fbm_overview AS

        SELECT
            fbm.id AS id,
            d.dealer_id AS dealer_id,
            d.name AS dealer_name,
            IFNULL(fbm.fb_username, 'n/a') AS fb_username,
            IFNULL((SELECT name FROM dealer_location WHERE dealer_location_id = fbm.dealer_location_id ), 'ALL') as location,
            GREATEST(
                (IFNULL((SELECT fbme_listings.listed_at FROM fbme_listings WHERE integration_id = fbm.id ORDER BY fbme_listings.listed_at DESC LIMIT 1),'1000-01-01 00:00:00')),
                (IFNULL((SELECT created_at FROM fbapp_errors WHERE marketplace_id = fbm.id ORDER BY created_at DESC LIMIT 1),'1000-01-01 00:00:00'))
            ) AS last_attempt_ts,
            (
				SELECT COUNT(1)>0 FROM fbme_listings WHERE integration_id = fbm.id AND DATE(listed_at) = GREATEST(
					(IFNULL((SELECT DATE(fbme_listings.listed_at) FROM fbme_listings WHERE integration_id = fbm.id ORDER BY fbme_listings.listed_at DESC LIMIT 1),'1000-01-01 00:00:00')),
					(IFNULL((SELECT DATE(created_at) FROM fbapp_errors WHERE marketplace_id = fbm.id AND error_type<>'missing-inventory' ORDER BY created_at DESC LIMIT 1),'1000-01-01 00:00:00'))
				)
            ) AS last_run_status,
            IFNULL((SELECT created_at FROM fbapp_errors WHERE marketplace_id=fbm.id ORDER BY id DESC LIMIT 1), '1000-01-01 00:00:00') AS last_known_error_ts,
            IFNULL((SELECT UC_Delimiter(REPLACE(error_type, '-', ' '), ' ', TRUE) FROM fbapp_errors WHERE marketplace_id=fbm.id ORDER BY id DESC LIMIT 1), 'no error') AS last_known_error_code,
            IFNULL((SELECT error_message FROM fbapp_errors WHERE marketplace_id=fbm.id ORDER BY id DESC LIMIT 1), 'no error') AS last_known_error_message,
            (IFNULL((SELECT fbme_listings.listed_at FROM fbme_listings WHERE integration_id = fbm.id ORDER BY fbme_listings.listed_at DESC LIMIT 1),'1000-01-01 00:00:00')) AS last_success_ts,

			IFNULL((SELECT GROUP_CONCAT(fbme_l0.SKU SEPARATOR ' | ') FROM fbme_listings AS fbme_l0 WHERE fbme_l0.integration_id=fbm.id AND DATE(fbme_l0.listed_at) = DATE(IFNULL((SELECT fbme_listings.listed_at FROM fbme_listings WHERE integration_id = fbm.id ORDER BY fbme_listings.listed_at DESC LIMIT 1),'1000-01-01 00:00:00'))), 'none') AS last_units_posted,

			IFNULL((SELECT GROUP_CONCAT(fbme_l0.SKU SEPARATOR ' | ') FROM fbme_listings AS fbme_l0 WHERE fbme_l0.integration_id=fbm.id AND DATE(fbme_l0.listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR))), 'none') AS units_posted_today,
            IFNULL((SELECT GROUP_CONCAT(fbme_l1.SKU SEPARATOR ' | ') FROM fbme_listings AS fbme_l1 WHERE fbme_l1.integration_id=fbm.id AND DATE(fbme_l1.listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR))), 'none') AS units_posted_1dayago,
            IFNULL((SELECT GROUP_CONCAT(fbme_l2.SKU SEPARATOR ' | ') FROM fbme_listings AS fbme_l2 WHERE fbme_l2.integration_id=fbm.id AND DATE(fbme_l2.listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR))), 'none') AS units_posted_2dayago,
            IFNULL((SELECT GROUP_CONCAT(fbme_l3.SKU SEPARATOR ' | ') FROM fbme_listings AS fbme_l3 WHERE fbme_l3.integration_id=fbm.id AND DATE(fbme_l3.listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR) ) ), 'none') AS units_posted_3dayago,
            IFNULL((SELECT GROUP_CONCAT(fbme_l4.SKU SEPARATOR ' | ') FROM fbme_listings AS fbme_l4 WHERE fbme_l4.integration_id=fbm.id AND DATE(fbme_l4.listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR))), 'none') AS units_posted_4dayago,
            IFNULL((SELECT GROUP_CONCAT(fbme_l5.SKU SEPARATOR ' | ') FROM fbme_listings AS fbme_l5 WHERE fbme_l5.integration_id=fbm.id AND DATE(fbme_l5.listed_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR))), 'none') AS units_posted_5dayago,
            IFNULL((SELECT error_message FROM fbapp_errors WHERE marketplace_id=fbm.id AND DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) ORDER BY id DESC LIMIT 1), 'no error') AS error_today,
            IFNULL((SELECT error_message FROM fbapp_errors WHERE marketplace_id=fbm.id AND DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) ORDER BY id DESC LIMIT 1), 'no error') AS error_1dayago,
            IFNULL((SELECT error_message FROM fbapp_errors WHERE marketplace_id=fbm.id AND DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) ORDER BY id DESC LIMIT 1), 'no error') AS error_2dayago,
            IFNULL((SELECT error_message FROM fbapp_errors WHERE marketplace_id=fbm.id AND DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) ORDER BY id DESC LIMIT 1), 'no error') AS error_3dayago,
            IFNULL((SELECT error_message FROM fbapp_errors WHERE marketplace_id=fbm.id AND DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) ORDER BY id DESC LIMIT 1), 'no error') AS error_4dayago,
            IFNULL((SELECT error_message FROM fbapp_errors WHERE marketplace_id=fbm.id AND DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) ORDER BY id DESC LIMIT 1), 'no error') AS error_5dayago

        FROM fbapp_marketplace AS fbm
        INNER JOIN dealer AS d ON fbm.dealer_id=d.dealer_id

        GROUP BY d.dealer_id, fbm.id

        ORDER BY last_attempt_ts DESC";
    }
}
