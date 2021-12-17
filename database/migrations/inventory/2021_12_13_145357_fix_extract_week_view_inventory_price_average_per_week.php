<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixExtractWeekViewInventoryPriceAveragePerWeek extends Migration
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
                             (SELECT date_trunc('week', created_at) FROM inventory_logs ORDER BY created_at LIMIT 1),
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
}
