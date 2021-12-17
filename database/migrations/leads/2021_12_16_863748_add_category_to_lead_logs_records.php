<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCategoryToLeadLogsRecords extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            UPDATE lead_logs AS l
            SET meta = jsonb_set(l.meta, '{category}', CONCAT('"',i.meta ->> 'category', '"')::jsonb, true)
            FROM inventory_logs i WHERE l.trailercentral_id = i.trailercentral_id AND l.meta->>'category' IS NULL
SQL
        );

        DB::statement(<<<SQL
          CREATE INDEX leads_category ON lead_logs((meta->>'category'));
SQL
        );

        DB::statement(<<<SQL
          CREATE INDEX leads_manufacturer_category ON lead_logs(manufacturer, (meta->>'category'));
SQL
        );

        DB::statement(<<<SQL
          CREATE INDEX leads_manufacturer_category_date ON lead_logs(manufacturer, (meta->>'category'), (created_at::date));
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_logs', function (Blueprint $table) {
            $table->dropIndex('leads_category');
            $table->dropIndex('leads_manufacturer_category');
            $table->dropIndex('leads_manufacturer_category_date');
        });
    }
}
