<?php

use Illuminate\Database\Migrations\Migration;

class AlterImageFilenameNoverlayLength extends Migration
{
    public function up(): void
    {
        // to be consistent with `filename` and `filename_noverlay`
        DB::statement('ALTER TABLE image MODIFY COLUMN filename_noverlay varchar(350) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE image MODIFY COLUMN filename_noverlay TEXT NULL');
    }
}
