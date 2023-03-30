<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageFilenameCompanions extends Migration
{
    public function up(): void
    {
        Schema::table('image', static function (Blueprint $table): void {
            if (!Schema::hasColumn('image', 'filename_with_overlay')) {
                $table->string('filename_with_overlay', 350)->after('filename')->nullable();
            }

            if (!Schema::hasColumn('image', 'filename_without_overlay')) {
                $table->string('filename_without_overlay', 350)
                    ->after('filename')
                    ->nullable()
                    ->comment('different from `filename_original`, this will contain the original image URL, also will help to get rid of `filename_noverlay` in a later solution');
            }
        });
    }

    public function down(): void
    {
        Schema::table('image', static function (Blueprint $table): void {
            $table->dropColumn('filename_without_overlay');
            $table->dropColumn('filename_with_overlay');
        });
    }
}
