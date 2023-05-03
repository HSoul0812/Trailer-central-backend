<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIndexEventStatusPerDayOnInventoryLogs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE INDEX inventory_logs_i_manufacturer_event_status_day
                ON inventory_logs (manufacturer, event, status, (created_at::date));
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX inventory_logs_i_manufacturer_event_status_day');
    }
}
