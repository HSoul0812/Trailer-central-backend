<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCategoryIndexInventoryLogs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
          CREATE INDEX inventory_category ON inventory_logs((meta->>'category'));
SQL
        );

        DB::statement(<<<SQL
          CREATE INDEX inventory_manufacturer_category ON inventory_logs(manufacturer, (meta->>'category'));
SQL
        );

        DB::statement(<<<SQL
          CREATE INDEX inventory_manufacturer_category_event ON inventory_logs(manufacturer, (meta->>'category'), event);
SQL
        );

        DB::statement(<<<SQL
          CREATE INDEX inventory_manufacturer_category_event_status ON inventory_logs(manufacturer, (meta->>'category'), event, status);
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->dropIndex('inventory_category');
            $table->dropIndex('inventory_manufacturer_category');
            $table->dropIndex('inventory_manufacturer_category_event');
            $table->dropIndex('inventory_manufacturer_category_event_status');
        });
    }
}
