<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterImageFilenamesIndices extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX image_filename_original_index ON image (filename_original)');
        DB::statement('CREATE INDEX image_filename_index ON image (filename)');
        DB::statement('CREATE INDEX image_filename_noverlay_index ON image (filename_noverlay)');
    }

    public function down(): void
    {
        Schema::table('image', static function (Blueprint $table): void {
            $table->dropIndex('image_filename_original_index');
            $table->dropIndex('image_filename_index');
            $table->dropIndex('image_filename_overlay_index');
        });
    }
}
