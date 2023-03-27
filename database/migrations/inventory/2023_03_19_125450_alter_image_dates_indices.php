<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterImageDatesIndices extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX image_created_at_index ON image (created_at)');
        DB::statement('CREATE INDEX image_updated_at_index ON image (updated_at)');
    }

    public function down(): void
    {
        Schema::table('image', static function (Blueprint $table): void {
            $table->dropIndex('image_created_at_index');
            $table->dropIndex('image_updated_at_index');
        });
    }
}
