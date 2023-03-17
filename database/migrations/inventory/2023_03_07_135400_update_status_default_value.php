<?php

use App\Models\Inventory\Inventory;
use Illuminate\Database\Migrations\Migration;

class UpdateStatusDefaultValue extends Migration
{
    /**
     * Run the migrations.
     * Prevent Collectors and other routines fail add default value to status
     * @return void
     */
    public function up(): void
    {
        // Set any inventory with status = null to 1
        Inventory::whereNull('status')->update([
            'status' => 1,
        ]);

        // Use DB statement to prevent Laravel from throwing exception regarding enum type
        DB::statement('ALTER TABLE `inventory` CHANGE COLUMN `status` `status` INT(255) NOT NULL DEFAULT 1 AFTER `description_html`');
    }
}
