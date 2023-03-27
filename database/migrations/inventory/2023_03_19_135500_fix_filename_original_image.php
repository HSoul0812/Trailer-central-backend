<?php

use Illuminate\Database\Migrations\Migration;

class FixFilenameOriginalImage extends Migration
{
    public function up(): void
    {
        // to be able to swap the image with/without overlay to filename we need to have them stored as concrete columns
        $updateFilenameCompanionsSQL = <<<SQL
            UPDATE inventory_image ii
            JOIN image i on ii.image_id = i.image_id
            SET filename_without_overlay = IF(filename_noverlay IS NOT NULL AND filename_noverlay != '', filename_noverlay, filename),
            filename_with_overlay = IF(filename_noverlay IS NOT NULL AND filename_noverlay != '', filename, NULL)
SQL;

        DB::statement($updateFilenameCompanionsSQL);
    }

    public function down(): void
    {
        // nothing to do
    }
}
