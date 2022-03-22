<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


class UpdateTrailerworldGooseneckPullType extends Migration
{

    private const QueryUpdateAttr = <<<'SQL'
INSERT IGNORE INTO `eav_attribute_value` (`attribute_id`, `inventory_id`, `value`)
SELECT
	3 AS attribute_id,
	inv.inventory_id,
	"gooseneck" AS `value`
FROM
	eav_attribute_value AS eavav
	RIGHT OUTER JOIN (
	SELECT
		i.inventory_id,
		i.category,
		i.entity_type_id,
		i.dealer_id,
		i.active,
		i.model,
		i.vin,
		i.year
	FROM
		inventory AS i
		JOIN ( SELECT DISTINCT
				TRIM(substring_index(substring_index(`data`, '"vin":"', -1), '","description"', 1)) AS `vin`
			FROM
				transaction_execute_queue
			WHERE
				`api` = 'trailerworld'
				AND(`data` LIKE '%"hitch_type":"Gooseneck"%'
					AND `data` LIKE '%"category":"Gooseneck%')
				AND queued_at > '2021-12-31') AS teq ON teq.vin = i.vin
			AND i.dealer_id = 11320) AS inv ON inv.inventory_id = eavav.inventory_id
	AND eavav.attribute_id = 3;
SQL;

    private const QueryUpdateInv = <<<'SQL'
UPDATE
	inventory AS ii
	JOIN ( SELECT DISTINCT
			TRIM(substring_index(substring_index(`data`, '"vin":"', - 1), '","description"', 1)) AS `vin`
		FROM
			transaction_execute_queue
		WHERE
			`api` = 'trailerworld'
			AND(`data` LIKE '%"hitch_type":"Gooseneck"%'
				AND `data` LIKE '%"category":"Gooseneck%')
			AND queued_at > '2021-12-31') AS teq ON ii.vin = teq.vin
		AND ii.dealer_id = 11320
SET ii.category = 'gooseneck_bodies';
SQL;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            DB::statement(self::QueryUpdateAttr);
            DB::statement(self::QueryUpdateInv);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
