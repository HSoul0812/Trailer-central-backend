<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageFilenameCompanions extends Migration
{
    public function up(): void
    {
        Schema::table('image', static function (Blueprint $table): void {
            $table->string('filename_with_overlay', 350)->after('filename')->nullable();
            $table->index('filename_with_overlay', 'image_filename_with_overlay_index');

            $table->string('filename_without_overlay', 350)
                ->after('filename')
                ->nullable()
                ->comment('different from `filename_original`, this will contain the original image URL, also will help to get rid of `filename_noverlay` in a later solution');

            $table->index('filename_without_overlay', 'image_filename_without_overlay_index');
        });
    }

    public function down(): void
    {
        Schema::table('image', static function (Blueprint $table): void {
            $table->dropIndex('image_filename_with_overlay_index');
            $table->dropIndex('image_filename_without_overlay_index');

            $table->dropColumn('filename_without_overlay');
            $table->dropColumn('filename_with_overlay');
        });
    }
}
