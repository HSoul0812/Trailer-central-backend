<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewInventoryStockAveragePerDay extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_stock_average_per_day AS
            WITH days as (
                SELECT day::date
                FROM generate_series(
                             (SELECT created_at FROM inventory_logs LIMIT 1),
                             NOW(),
                             '1 day'
                         ) as series(day)
            ), -- list of days since the very first record
            counters AS (
                 SELECT s.day,
                        l.manufacturer,
                        COUNT(l.manufacturer) filter (where l.created_at::date = s.day AND l.status = 'available') AS aggregate,
                        EXISTS(
                                (
                                    SELECT il.manufacturer
                                    FROM inventory_logs il
                                    WHERE l.manufacturer = il.manufacturer
                                      AND s.day = il.created_at::date
                                      AND il.status = 'available'
                                )
                        )
                 FROM days as s, inventory_logs l
                 GROUP BY s.day, l.manufacturer
                 ORDER BY s.day, l.manufacturer
            ) -- counters per day and manufacturer

            SELECT c.day,
                   c.manufacturer,
                   CASE
                       WHEN c.exists THEN c.aggregate
                       ELSE LAG(aggregate) OVER (PARTITION BY c.manufacturer ORDER BY c.day, c.manufacturer)
                   END AS aggregate -- in case there isn't any record for the manufacturer on the day, it will use a carrier
            FROM counters c
            ORDER BY c.day, c.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_day');
    }
}
