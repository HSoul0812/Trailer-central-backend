<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewInventoryStockAveragePerWeek extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_stock_average_per_week AS
            WITH weeks as (
                SELECT to_char(date, 'IYYY-IW') AS week
                FROM generate_series(
                             (SELECT created_at FROM inventory_logs LIMIT 1),
                             NOW(),
                             '1 WEEK'
                         ) as series(date)
            ), -- list of weeks from the first record
            counters AS (
                SELECT s.week,
                       l.manufacturer,
                       COUNT(l.manufacturer) filter (where to_char(l.created_at, 'IYYY-IW') = s.week AND l.status = 'available') AS aggregate,
                       EXISTS(
                               (
                                   SELECT il.manufacturer
                                   FROM inventory_logs il
                                   WHERE l.manufacturer = il.manufacturer
                                     AND s.week = to_char(il.created_at, 'IYYY-IW')
                                     AND il.status = 'available'
                               )
                            )
                FROM weeks as s, inventory_logs l
                GROUP BY s.week, l.manufacturer
                ORDER BY s.week, l.manufacturer
            ) -- counters per week and manufacturer

            SELECT c.week,
                   c.manufacturer,
                   CASE
                       WHEN c.exists THEN c.aggregate
                       ELSE LAG(aggregate) OVER (PARTITION BY c.manufacturer ORDER BY c.week, c.manufacturer)
                   END AS aggregate -- in case there isn't any record for the manufacturer on the week, it will use a carrier
            FROM counters c
            ORDER BY c.week, c.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_week');
    }
}
