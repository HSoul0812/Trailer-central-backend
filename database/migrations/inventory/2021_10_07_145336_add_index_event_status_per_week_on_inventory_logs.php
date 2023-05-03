<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIndexEventStatusPerWeekOnInventoryLogs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
             CREATE INDEX inventory_logs_i_manufacturer_event_status_week
             ON inventory_logs (
                                manufacturer,
                                event,
                                status,
                                (extract(isoyear from created_at) || '-' || lpad(extract(week from created_at)::text, 2, '0'))
                               );

SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX inventory_logs_i_manufacturer_event_status_week');
    }
}
