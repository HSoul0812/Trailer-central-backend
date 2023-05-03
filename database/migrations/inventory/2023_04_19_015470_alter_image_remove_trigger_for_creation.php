<?php

use Illuminate\Database\Migrations\Migration;

class AlterImageRemoveTriggerForCreation extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS inventory_image_before_insert');
    }

    public function down(): void
    {
        // we dont want that trigger anymore :)
    }
}
