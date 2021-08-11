<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class FixDuplicateDealerLocationNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::transaction(function () {
            $this->fixAll();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::transaction(function () {
            $this->rollback();
        });
    }

    private function fixAll(): void
    {
        foreach ($this->getDupLocations() as $dupLocation) {
            $i = 1;
            foreach ($this->getLocationsSortByRelevance($dupLocation->dealer_id, $dupLocation->_name) as $location) {
                if ($i !== 1) {
                    $this->fixLocationById($location->dealer_location_id, $location->_name);
                }

                $i++;
            }
        }
    }

    private function fixLocationById(int $id, string $name): void
    {
        DB::table('dealer_location')
            ->where('dealer_location_id', '=', $id)
            ->update(['name' => $name . ' #' . $id]);
    }

    private function getDupLocations(): LazyCollection
    {
        return DB::table('dealer_location')
            ->select('dealer_id')
            ->selectRaw('TRIM(name) AS _name, COUNT(dealer_id) AS total_locations')
            ->whereNull('deleted_at')
            ->groupBy('dealer_id', '_name')
            ->having('total_locations', '>', 1)
            ->cursor();
    }

    /**
     * List of locations sort by their relevance form higher to lower.
     *
     * The way to calculate the relevance is an heuristic approach, using the following discernment:
     *
     *  (a) Given the number of units sales related to the location
     *  (b) And the number of repair orders related to the location
     *
     *  (i)    If the location is the default location, its relevance will be the higher relevance,
     *         a higher number which probably wont be surpassed
     *  (ii)   If the location is the invoicing default location, its relevance will be high,
     *         but not enough to get over (i), it only could get over (i) when also it has enough related unit sales (a)
     *         or repair orders (b) to have a greater relevance than (i)
     *
     * @param int $dealerId
     * @param string $name
     * @return LazyCollection
     */
    private function getLocationsSortByRelevance(int $dealerId, string $name): LazyCollection
    {
        $sql = <<<SQL
             (SELECT dealer_location_id,
                       TRIM(name)                                                                                 AS _name,
                       IF(l.is_default = 1, 10000, 0)                                                             AS relevance_by_default,
                       IF(l.is_default_for_invoice = 1, 9800, 0)                                                  AS relevance_by_default_for_invoice,
                       (SELECT COUNT(u.id) FROM dms_unit_sale u WHERE u.sales_location_id = l.dealer_location_id) AS relevance_by_quotes,
                       (SELECT COUNT(b.id) FROM dms_repair_order b WHERE b.location = l.dealer_location_id)       AS relevance_by_ros
                FROM dealer_location l
                WHERE l.dealer_id = :dealer_id AND TRIM(l.name) = :name AND l.deleted_at IS NULL) AS locations
SQL;

        return DB::query()->fromRaw($sql, ['dealer_id' => $dealerId, 'name' => $name])
            ->selectRaw('*, (relevance_by_default + relevance_by_default_for_invoice + relevance_by_quotes + relevance_by_ros) AS relevance')
            ->orderBy('relevance', 'desc')
            ->cursor();
    }

    private function rollback(): void
    {
        $updaterCursor = function (Collection $locations): void {
            foreach ($locations as $location) {
                $parts = explode(' #', $location->name);
                $name = $parts[0];
                $id = $parts[2] ?? $parts[1];

                if (is_numeric($id) && (int)$location->dealer_location_id === (int)$id) {
                    DB::table('dealer_location')
                        ->where('dealer_location_id', '=', $id)
                        ->update(['name' => $name]);
                }
            }
        };

        DB::table('dealer_location')
            ->select('dealer_location_id', 'name')
            ->whereNull('deleted_at')
            ->whereRaw("name LIKE '% #%'")
            ->orderBy('dealer_location_id')
            ->chunkById(100, $updaterCursor, 'dealer_location_id');
    }
}
