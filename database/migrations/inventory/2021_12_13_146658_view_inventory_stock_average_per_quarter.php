<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ViewInventoryStockAveragePerQuarter extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW inventory_stock_average_per_quarter AS
            WITH quarters as (
                SELECT to_char(date, 'YYYY-"Q"Q') as quarter
                FROM generate_series(
                             (SELECT date_trunc('quarter', created_at)::date FROM inventory_logs ORDER BY created_at LIMIT 1),
                             NOW(),
                             '3 MONTH'
                         ) as series(date)
            ), -- list of quarters from the first record
            manufacturers as (SELECT l.manufacturer FROM inventory_logs l GROUP BY l.manufacturer)

            SELECT s.quarter,
                   m.manufacturer,
                   (
                    SELECT COUNT(l.id)
                    FROM inventory_logs l
                    LEFT JOIN inventory_logs sold ON sold.trailercentral_id = l.trailercentral_id AND sold.status = 'sold'
                    WHERE l.manufacturer = m.manufacturer AND l.event = 'created'
                      AND l.status = 'available'
                      AND to_char(l.created_at, 'YYYY-"Q"Q') <= s.quarter
                      AND (to_char(sold.created_at, 'YYYY-"Q"Q') > s.quarter OR sold.created_at IS NULL)
                   ) AS aggregate
            FROM quarters as s
            CROSS JOIN manufacturers m
            GROUP BY s.quarter, m.manufacturer
            ORDER BY s.quarter, m.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS inventory_stock_average_per_quarter');
    }
}
