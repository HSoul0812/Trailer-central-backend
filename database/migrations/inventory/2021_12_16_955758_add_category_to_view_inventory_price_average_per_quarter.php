<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCategoryToViewInventoryPriceAveragePerQuarter extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_price_average_per_quarter');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_price_average_per_quarter AS
            WITH quarters as (
                SELECT to_char(date, 'YYYY-"Q"Q') as quarter
                FROM generate_series(
                             (SELECT date_trunc('quarter', created_at)::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '3 month'
                         ) as series(date)
            ), -- list of quarters since the very first record
            manufacturers as (SELECT l.manufacturer,
                                     l.meta->>'category' as category
                              FROM inventory_logs l GROUP BY l.manufacturer, l.meta->>'category')

            SELECT s.quarter,
                   m.manufacturer,
                   m.category,
                   (
                    SELECT AVG(l.price)
                    FROM inventory_logs l
                    WHERE l.manufacturer = m.manufacturer AND l.meta->>'category' = m.category AND l.event IN ('created', 'price-changed')
                      AND to_char(l.created_at, 'IYYY-"Q"Q') <= s.quarter
                   ) AS aggregate
             FROM quarters as s
             CROSS JOIN manufacturers m
             GROUP BY s.quarter, m.category, m.manufacturer
             ORDER BY s.quarter, m.category, m.manufacturer;
             -- averages per quarter and manufacturer
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_price_average_per_quarter');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_price_average_per_quarter AS
            WITH quarters as (
                SELECT to_char(date, 'YYYY-"Q"Q') as quarter
                FROM generate_series(
                             (SELECT date_trunc('quarter', created_at)::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '3 month'
                         ) as series(date)
            ), -- list of quarters since the very first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.quarter,
                   m.manufacturer,
                   (
                    SELECT AVG(l.price)
                    FROM inventory_logs l
                    WHERE l.manufacturer = m.manufacturer AND l.event IN ('created', 'price-changed')
                      AND to_char(l.created_at, 'IYYY-"Q"Q') <= s.quarter
                   ) AS aggregate
             FROM quarters as s
             CROSS JOIN manufacturers m
             GROUP BY s.quarter, m.manufacturer
             ORDER BY s.quarter, m.manufacturer;
             -- averages per quarter and manufacturer
SQL
        );
    }
}
