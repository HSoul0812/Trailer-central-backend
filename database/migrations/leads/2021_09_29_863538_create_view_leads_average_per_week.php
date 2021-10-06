<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class CreateViewLeadsAveragePerWeek extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE MATERIALIZED VIEW leads_average_per_week AS
            WITH weeks as (
                SELECT to_char(date, 'IYYY-IW') AS week
                FROM generate_series(
                             (SELECT submitted_at FROM lead_logs LIMIT 1),
                             NOW(),
                             '1 WEEK'
                         ) as series(date)
            ), -- list of weeks from the first record
            counters AS (
                SELECT s.week,
                       l.manufacturer,
                       COUNT(l.id) filter (where to_char(l.submitted_at, 'IYYY-IW') = s.week) AS aggregate,
                       EXISTS(
                               (
                                   SELECT il.manufacturer
                                   FROM lead_logs il
                                   WHERE l.manufacturer = il.manufacturer
                                     AND s.week = to_char(il.submitted_at, 'IYYY-IW')
                               )
                            )
                FROM weeks as s, lead_logs l
                GROUP BY s.week, l.manufacturer
                ORDER BY s.week, l.manufacturer
            ) -- counters per week and manufacturer

            SELECT c.week,
                   c.manufacturer,
                   CASE
                       WHEN c.exists THEN c.aggregate
                       ELSE LAG(aggregate) OVER (PARTITION BY c.manufacturer ORDER BY c.week, c.manufacturer)
                   END AS aggregate -- in case there isn't any record for the manufacturer on the week, it will use a carrier
            FROM counters c
            ORDER BY c.week, c.manufacturer;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS leads_average_per_week');
    }
}
