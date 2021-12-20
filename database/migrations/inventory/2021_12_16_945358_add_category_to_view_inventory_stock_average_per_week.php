<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCategoryToViewInventoryStockAveragePerWeek extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_week');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_stock_average_per_week AS
            WITH weeks as (
                SELECT to_char(date, 'IYYY-IW') AS week
                FROM generate_series(
                             (SELECT date_trunc('week', created_at) FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 WEEK'
                         ) as series(date)
            ), -- list of weeks from the first record
            manufacturers as (SELECT l.manufacturer,
                                     l.meta->>'category' as category
                              FROM inventory_logs l GROUP BY l.manufacturer, l.meta->>'category')

            SELECT s.week,
                   m.manufacturer,
                   m.category,
                   (
                    SELECT COUNT(l.id)
                    FROM inventory_logs l
                    LEFT JOIN inventory_logs sold ON sold.trailercentral_id = l.trailercentral_id AND sold.status = 'sold'
                    WHERE l.manufacturer = m.manufacturer AND l.meta->>'category' = m.category AND l.event = 'created'
                      AND l.status = 'available'
                      AND to_char(l.created_at, 'IYYY-IW') <= s.week
                      AND (to_char(sold.created_at, 'IYYY-IW') > s.week OR sold.created_at IS NULL)
                   ) AS aggregate
            FROM weeks as s
            CROSS JOIN manufacturers m
            GROUP BY s.week, m.category, m.manufacturer
            ORDER BY s.week, m.category, m.manufacturer;
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
            CREATE MATERIALIZED VIEW inventory_stock_average_per_week AS
            WITH weeks as (
                SELECT to_char(date, 'IYYY-IW') AS week
                FROM generate_series(
                             (SELECT created_at FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 WEEK'
                         ) as series(date)
            ), -- list of weeks from the first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.week,
                   m.manufacturer,
                   (
                    SELECT COUNT(l.id)
                    FROM inventory_logs l
                    LEFT JOIN inventory_logs sold ON sold.trailercentral_id = l.trailercentral_id AND sold.status = 'sold'
                    WHERE l.manufacturer = m.manufacturer AND l.event = 'created'
                      AND l.status = 'available'
                      AND to_char(l.created_at, 'IYYY-IW') <= s.week
                      AND (to_char(sold.created_at, 'IYYY-IW') > s.week OR sold.created_at IS NULL)
                   ) AS aggregate
            FROM weeks as s
            CROSS JOIN manufacturers m
            GROUP BY s.week, m.manufacturer
            ORDER BY s.week, m.manufacturer;
SQL
        );
    }
}
