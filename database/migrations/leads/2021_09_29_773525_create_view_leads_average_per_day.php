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
                             (SELECT submitted_at FROM lead_logs LIMIT 1),
                             NOW(),
                             '1 day'
                         ) as series(day)
            ), -- list of days since the very first record
            averages AS (
                SELECT s.day,
                       l.manufacturer,
                       COUNT(l.id) filter (where l.submitted_at::date = s.day) AS aggregate,
                       EXISTS(
                               (
                                   SELECT il.manufacturer
                                   FROM lead_logs il
                                   WHERE l.manufacturer = il.manufacturer
                                     AND s.day = il.submitted_at::date
                               )
                           )
                FROM days as s, lead_logs l
                GROUP BY s.day, l.manufacturer
                ORDER BY s.day, l.manufacturer
            ) -- averages per day and manufacturer

            SELECT a.day,
                   a.manufacturer,
                   CASE
                       WHEN a.exists THEN a.aggregate
                       ELSE LAG(aggregate) OVER (PARTITION BY a.manufacturer ORDER BY a.day, a.manufacturer)
                       END AS aggregate -- in case there isn't any record for the manufacturer on the day, it will use a carrier
            FROM averages a
            ORDER BY a.day, a.manufacturer;
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
