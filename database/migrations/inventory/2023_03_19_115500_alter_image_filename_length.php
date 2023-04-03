<?php

use Illuminate\Database\Migrations\Migration;

class AlterImageFilenameLength extends Migration
{
    public function up(): void
    {
        // to be consistent with `filename` and `filename_noverlay`
        DB::statement('ALTER TABLE image MODIFY COLUMN filename varchar(350) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE image MODIFY COLUMN filename TEXT NULL');
    }
}
