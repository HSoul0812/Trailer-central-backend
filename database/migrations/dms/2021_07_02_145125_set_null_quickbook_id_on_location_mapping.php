<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class SetNullQuickbookIdOnLocationMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `location_quickbooks_mapping` MODIFY `quickbooks_id` int NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `location_quickbooks_mapping` MODIFY `quickbooks_id` int NOT NULL');
    }
}
