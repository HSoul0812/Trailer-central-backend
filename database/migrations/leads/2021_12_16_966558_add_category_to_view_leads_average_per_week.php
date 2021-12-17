<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class AddCategoryToViewLeadsAveragePerWeek extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_week');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_week AS
            WITH weeks as (
                SELECT to_char(date, 'IYYY-IW') AS week
                FROM generate_series(
                             (SELECT date_trunc('week', submitted_at) FROM lead_logs LIMIT 1),
                             NOW(),
                             '1 WEEK'
                         ) as series(date)
            ), -- list of weeks from the first record
            manufacturers as (SELECT l.manufacturer,
                                     l.meta->>'category' as category
                              FROM inventory_logs l GROUP BY l.manufacturer, l.meta->>'category')

            SELECT s.week ,
               m.manufacturer,
               m.category,
               COUNT(l.id) AS aggregate
            FROM weeks as s
            CROSS JOIN manufacturers m
            LEFT JOIN lead_logs l ON l.manufacturer = m.manufacturer AND l.meta->>'category' = m.category AND to_char(l.submitted_at, 'IYYY-IW') = s.week
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
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_week');

        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_week AS
            WITH weeks as (
                SELECT to_char(date, 'IYYY-IW') AS week
                FROM generate_series(
                             (SELECT date_trunc('week', submitted_at) FROM lead_logs LIMIT 1),
                             NOW(),
                             '1 WEEK'
                         ) as series(date)
            ), -- list of weeks from the first record
            manufacturers as (SELECT l.manufacturer FROM lead_logs l GROUP BY l.manufacturer)

            SELECT s.week ,
               m.manufacturer,
               COUNT(l.id) AS aggregate
            FROM weeks as s
            CROSS JOIN manufacturers m
            LEFT JOIN lead_logs l ON l.manufacturer = m.manufacturer AND to_char(l.submitted_at, 'IYYY-IW') = s.week
            GROUP BY s.week, m.manufacturer
            ORDER BY s.week, m.manufacturer;
SQL
        );
    }
}
