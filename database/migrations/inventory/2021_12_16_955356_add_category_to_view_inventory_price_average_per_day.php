<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCategoryToViewInventoryPriceAveragePerDay extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_price_average_per_day');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_price_average_per_day AS
            WITH days as (
                SELECT day::date
                FROM generate_series(
                             (SELECT created_at FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 day'
                         ) as series(day)
            ), -- list of days since the very first record
            manufacturers as (SELECT l.manufacturer,
                                     l.meta->>'category' as category
                              FROM inventory_logs l GROUP BY l.manufacturer, l.meta->>'category')

            SELECT s.day,
                   m.manufacturer,
                   m.category,
                   (
                    SELECT AVG(l.price)
                    FROM inventory_logs l
                    WHERE l.manufacturer = m.manufacturer AND l.meta->>'category' = m.category AND l.event IN ('created', 'price-changed')
                      AND l.created_at::date <= s.day
                   ) AS aggregate
            FROM days as s
            CROSS JOIN manufacturers m
            GROUP BY s.day, m.category, m.manufacturer
            ORDER BY s.day, m.category, m.manufacturer;
            -- averages per day and manufacturer

SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_price_average_per_day');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_price_average_per_day AS
            WITH days as (
                SELECT day::date
                FROM generate_series(
                             (SELECT created_at FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 day'
                         ) as series(day)
            ), -- list of days since the very first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.day,
                   m.manufacturer,
                   (
                    SELECT AVG(l.price)
                    FROM inventory_logs l
                    WHERE l.manufacturer = m.manufacturer AND l.event IN ('created', 'price-changed')
                      AND l.created_at::date <= s.day
                   ) AS aggregate
            FROM days as s
            CROSS JOIN manufacturers m
            GROUP BY s.day, m.manufacturer
            ORDER BY s.day, m.manufacturer;
            -- averages per day and manufacturer

SQL
        );
    }
}
