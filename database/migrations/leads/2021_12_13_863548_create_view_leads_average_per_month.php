<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class CreateViewLeadsAveragePerMonth extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_month AS
            WITH months as (
                SELECT to_char(date, 'YYYY-MM') AS month
                FROM generate_series(
                            (SELECT TO_CHAR(submitted_at, 'yyyy-mm-01')::date FROM lead_logs LIMIT 1),
                             NOW(),
                             '1 MONTH'
                         ) as series(date)
            ), -- list of months from the first record
            manufacturers as (SELECT l.manufacturer FROM lead_logs l GROUP BY l.manufacturer)

            SELECT s.month ,
               m.manufacturer,
               COUNT(l.id) AS aggregate
            FROM months as s
            CROSS JOIN manufacturers m
            LEFT JOIN lead_logs l ON l.manufacturer = m.manufacturer AND to_char(l.submitted_at, 'YYYY-MM') = s.month
            GROUP BY s.month, m.manufacturer
            ORDER BY s.month, m.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_month');
    }
}
