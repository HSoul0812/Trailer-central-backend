<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class AddCategoryToViewLeadsAveragePerYear extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_year');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_year AS
            WITH years as (
                SELECT to_char(date, 'YYYY') AS year
                FROM generate_series(
                            (SELECT date_trunc('year', submitted_at)::date FROM lead_logs LIMIT 1),
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
               COUNT(l.id) AS aggregate
            FROM years as s
            CROSS JOIN manufacturers m
            LEFT JOIN lead_logs l ON l.manufacturer = m.manufacturer AND l.meta->>'category' = m.category AND to_char(l.submitted_at, 'YYYY') = s.year
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
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_year');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_year AS
            WITH years as (
                SELECT to_char(date, 'YYYY') AS year
                FROM generate_series(
                            (SELECT date_trunc('year', submitted_at)::date FROM lead_logs LIMIT 1),
                             NOW(),
                             '1 YEAR'
                         ) as series(date)
            ), -- list of years from the first record
            manufacturers as (SELECT l.manufacturer FROM lead_logs l GROUP BY l.manufacturer)

            SELECT s.year,
               m.manufacturer,
               COUNT(l.id) AS aggregate
            FROM years as s
            CROSS JOIN manufacturers m
            LEFT JOIN lead_logs l ON l.manufacturer = m.manufacturer AND to_char(l.submitted_at, 'YYYY') = s.year
            GROUP BY s.year, m.manufacturer
            ORDER BY s.year, m.manufacturer;
SQL
        );
    }
}
