<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ViewInventoryPriceAveragePerYear extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_price_average_per_year AS
            WITH years as (
                SELECT to_char(date, 'YYYY') as year
                FROM generate_series(
                             (SELECT date_trunc('year', created_at)::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '1 year'
                         ) as series(date)
            ), -- list of years since the very first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.year,
                   m.manufacturer,
                   (
                    SELECT AVG(l.price)
                    FROM inventory_logs l
                    WHERE l.manufacturer = m.manufacturer AND l.event IN ('created', 'price-changed')
                      AND to_char(l.created_at, 'YYYY') <= s.year
                   ) AS aggregate
             FROM years as s
             CROSS JOIN manufacturers m
             GROUP BY s.year, m.manufacturer
             ORDER BY s.year, m.manufacturer;
             -- averages per year and manufacturer
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_price_average_per_year');
    }
}
