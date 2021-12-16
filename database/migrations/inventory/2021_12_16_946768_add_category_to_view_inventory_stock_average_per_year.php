<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCategoryToViewInventoryStockAveragePerYear extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_year');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_stock_average_per_year AS
            WITH years as (
                SELECT to_char(date, 'YYYY') as year
                FROM generate_series(
                             (SELECT date_trunc('year', created_at)::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 YEAR'
                         ) as series(date)
            ), -- list of years from the first record
            manufacturers as (SELECT l.manufacturer,
                                     l.meta->>'category' as category
                              FROM inventory_logs l GROUP BY l.manufacturer, l.meta->>'category')

            SELECT s.year,
                   m.manufacturer,
                   m.category,
                   (
                    SELECT COUNT(l.id)
                    FROM inventory_logs l
                    LEFT JOIN inventory_logs sold ON sold.trailercentral_id = l.trailercentral_id AND sold.status = 'sold'
                    WHERE l.manufacturer = m.manufacturer AND l.meta->>'category' = m.category AND l.event = 'created'
                      AND l.status = 'available'
                      AND to_char(l.created_at, 'YYYY') <= s.year
                      AND (to_char(sold.created_at, 'YYYY') > s.year OR sold.created_at IS NULL)
                   ) AS aggregate
            FROM years as s
            CROSS JOIN manufacturers m
            GROUP BY s.year, m.category, m.manufacturer
            ORDER BY s.year, m.category, m.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_year');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_stock_average_per_year AS
            WITH years as (
                SELECT to_char(date, 'YYYY') as year
                FROM generate_series(
                             (SELECT date_trunc('year', created_at)::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 YEAR'
                         ) as series(date)
            ), -- list of years from the first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.year,
                   m.manufacturer,
                   (
                    SELECT COUNT(l.id)
                    FROM inventory_logs l
                    LEFT JOIN inventory_logs sold ON sold.trailercentral_id = l.trailercentral_id AND sold.status = 'sold'
                    WHERE l.manufacturer = m.manufacturer AND l.event = 'created'
                      AND l.status = 'available'
                      AND to_char(l.created_at, 'YYYY') <= s.year
                      AND (to_char(sold.created_at, 'YYYY') > s.year OR sold.created_at IS NULL)
                   ) AS aggregate
            FROM years as s
            CROSS JOIN manufacturers m
            GROUP BY s.year, m.manufacturer
            ORDER BY s.year, m.manufacturer;
SQL
        );
    }
}
