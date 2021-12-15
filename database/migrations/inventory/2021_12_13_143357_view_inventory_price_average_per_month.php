<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ViewInventoryPriceAveragePerMonth extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_price_average_per_month AS
            WITH months as (
                SELECT to_char(date, 'YYYY-MM') AS month
                FROM generate_series(
                             (SELECT TO_CHAR(created_at, 'yyyy-mm-01')::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 month'
                         ) as series(date)
            ), -- list of months since the very first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.month,
                   m.manufacturer,
                   (
                    SELECT AVG(l.price)
                    FROM inventory_logs l
                    WHERE l.manufacturer = m.manufacturer AND l.event IN ('created', 'price-changed')
                      AND to_char(l.created_at, 'IYYY-MM') <= s.month
                   ) AS aggregate
             FROM months as s
             CROSS JOIN manufacturers m
             GROUP BY s.month, m.manufacturer
             ORDER BY s.month, m.manufacturer;
             -- averages per month and manufacturer
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_price_average_per_month');
    }
}
