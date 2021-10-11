<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewLeadsAveragePerDay extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_day AS
            WITH days as (
                SELECT day::date
                FROM generate_series(
                             (SELECT submitted_at FROM lead_logs ORDER BY submitted_at LIMIT 1),
                             NOW(),
                             '1 day'
                         ) as series(day)
            ), -- list of days since the very first record
            manufacturers as (SELECT l.manufacturer FROM lead_logs l GROUP BY l.manufacturer)

            SELECT s.day ,
                   m.manufacturer,
                   COUNT(l.id) AS aggregate
            FROM days as s
            CROSS JOIN manufacturers m
            LEFT JOIN lead_logs l ON l.manufacturer = m.manufacturer AND l.created_at::date = s.day
            GROUP BY s.day, m.manufacturer
            ORDER BY s.day, m.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_day');
    }
}
