<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixViewInventoryPriceAveragePerWeek extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_price_average_per_week');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_price_average_per_week AS
            WITH weeks as (
                SELECT to_char(date, 'IYYY-IW') AS week
                FROM generate_series(
                             (SELECT created_at FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 week'
                         ) as series(date)
            ), -- list of weeks since the very first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.week,
                   m.manufacturer,
                   (
                    SELECT AVG(l.price)
                    FROM inventory_logs l
                    WHERE l.manufacturer = m.manufacturer AND l.event IN ('created', 'price-changed')
                      AND to_char(l.created_at, 'IYYY-IW') <= s.week
                   ) AS aggregate
             FROM weeks as s
             CROSS JOIN manufacturers m
             GROUP BY s.week, m.manufacturer
             ORDER BY s.week, m.manufacturer;
             -- averages per week and manufacturer
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_week');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_price_average_per_week AS
            WITH days as (
                SELECT to_char(date, 'IYYY-IW') AS week
                FROM generate_series(
                             (SELECT created_at FROM inventory_logs LIMIT 1),
                             NOW(),
                             '1 week'
                         ) as series(date)
            ), -- list of weeks since the very first record
            averages AS (
                 SELECT s.week,
                        l.manufacturer,
                        AVG(l.price) filter (where to_char(l.created_at, 'IYYY-IW') = s.week AND (l.event IN ('created', 'price-changed'))) AS aggregate,
                        EXISTS(
                                (
                                    SELECT il.manufacturer
                                    FROM inventory_logs il
                                    WHERE l.manufacturer = il.manufacturer
                                      AND s.week = to_char(il.created_at, 'IYYY-IW')
                                      AND (il.event IN ('created', 'price-changed'))
                                )
                            )
                 FROM days as s, inventory_logs l
                 GROUP BY s.week, l.manufacturer
                 ORDER BY s.week, l.manufacturer
            ) -- counters per week and manufacturer

            SELECT a.week,
                   a.manufacturer,
                   CASE
                       WHEN a.exists THEN a.aggregate
                       ELSE LAG(aggregate) OVER (PARTITION BY a.manufacturer ORDER BY a.week, a.manufacturer)
                       END AS aggregate -- in case there isn't any record for the manufacturer on the day, it will use a carrier
            FROM averages a
            ORDER BY a.week, a.manufacturer;
SQL
        );
    }
}
