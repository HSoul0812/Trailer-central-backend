<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class AddCategoryToViewLeadsAveragePerQuarter extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_quarter');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_quarter AS
            WITH quarters as (
                SELECT to_char(date, 'YYYY-"Q"Q') AS quarter
                FROM generate_series(
                            (SELECT date_trunc('quarter', submitted_at)::date FROM lead_logs LIMIT 1),
                             NOW(),
                             '3 MONTH'
                         ) as series(date)
            ), -- list of quarters from the first record
            manufacturers as (SELECT l.manufacturer,
                                     l.meta->>'category' as category
                              FROM inventory_logs l GROUP BY l.manufacturer, l.meta->>'category')

            SELECT s.quarter,
               m.manufacturer,
               m.category,
               COUNT(l.id) AS aggregate
            FROM quarters as s
            CROSS JOIN manufacturers m
            LEFT JOIN lead_logs l ON l.manufacturer = m.manufacturer AND l.meta->>'category' = m.category AND to_char(l.submitted_at, 'YYYY-"Q"Q') = s.quarter
            GROUP BY s.quarter, m.category, m.manufacturer
            ORDER BY s.quarter, m.category, m.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_quarter');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_quarter AS
            WITH quarters as (
                SELECT to_char(date, 'YYYY-"Q"Q') AS quarter
                FROM generate_series(
                            (SELECT date_trunc('quarter', submitted_at)::date FROM lead_logs LIMIT 1),
                             NOW(),
                             '3 MONTH'
                         ) as series(date)
            ), -- list of quarters from the first record
            manufacturers as (SELECT l.manufacturer FROM lead_logs l GROUP BY l.manufacturer)

            SELECT s.quarter,
               m.manufacturer,
               COUNT(l.id) AS aggregate
            FROM quarters as s
            CROSS JOIN manufacturers m
            LEFT JOIN lead_logs l ON l.manufacturer = m.manufacturer AND to_char(l.submitted_at, 'YYYY-"Q"Q') = s.quarter
            GROUP BY s.quarter, m.manufacturer
            ORDER BY s.quarter, m.manufacturer;
SQL
        );
    }
}
