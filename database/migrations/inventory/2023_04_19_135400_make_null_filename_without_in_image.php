<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeNullFilenameWithoutInImage extends Migration
{
    public function up(): void
    {
        // this is necessary to fix what database/migrations/inventory/2023_03_19_135500_fix_filename_original_image.php
        // database/migrations/inventory/2023_03_19_115470_alter_image_add_trigger_for_creation.php and
        // App\Observers\Inventory\ImageObserver::creating have done

        if (!App::environment('production')) {
            DB::statement(
                'UPDATE image SET filename_without_overlay = NULL WHERE filename_without_overlay IS NOT NULL'
            );
        }
    }

    public function down(): void
    {
        // nothing to do
    }
}
