<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCategoryToViewInventoryStockAveragePerMonth extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_month');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_stock_average_per_month AS
            WITH months as (
                SELECT to_char(date, 'YYYY-MM') AS month
                FROM generate_series(
                             (SELECT TO_CHAR(created_at, 'yyyy-mm-01')::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 MONTH'
                         ) as series(date)
            ), -- list of months from the first record
            manufacturers as (SELECT l.manufacturer,
                                     l.meta->>'category' as category
                              FROM inventory_logs l GROUP BY l.manufacturer, l.meta->>'category')

            SELECT s.month,
                   m.manufacturer,
                   m.category,
                   (
                    SELECT COUNT(l.id)
                    FROM inventory_logs l
                    LEFT JOIN inventory_logs sold ON sold.trailercentral_id = l.trailercentral_id AND sold.status = 'sold'
                    WHERE l.manufacturer = m.manufacturer AND l.meta->>'category' = m.category AND l.event = 'created'
                      AND l.status = 'available'
                      AND to_char(l.created_at, 'YYYY-MM') <= s.month
                      AND (to_char(sold.created_at, 'YYYY-MM') > s.month OR sold.created_at IS NULL)
                   ) AS aggregate
            FROM months as s
            CROSS JOIN manufacturers m
            GROUP BY s.month, m.category, m.manufacturer
            ORDER BY s.month, m.category, m.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_month');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_stock_average_per_month AS
            WITH months as (
                SELECT to_char(date, 'YYYY-MM') AS month
                FROM generate_series(
                             (SELECT TO_CHAR(created_at, 'yyyy-mm-01')::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 MONTH'
                         ) as series(date)
            ), -- list of months from the first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.month,
                   m.manufacturer,
                   (
                    SELECT COUNT(l.id)
                    FROM inventory_logs l
                    LEFT JOIN inventory_logs sold ON sold.trailercentral_id = l.trailercentral_id AND sold.status = 'sold'
                    WHERE l.manufacturer = m.manufacturer AND l.event = 'created'
                      AND l.status = 'available'
                      AND to_char(l.created_at, 'YYYY-MM') <= s.month
                      AND (to_char(sold.created_at, 'YYYY-MM') > s.month OR sold.created_at IS NULL)
                   ) AS aggregate
            FROM months as s
            CROSS JOIN manufacturers m
            GROUP BY s.month, m.manufacturer
            ORDER BY s.month, m.manufacturer;
SQL
        );
    }
}
