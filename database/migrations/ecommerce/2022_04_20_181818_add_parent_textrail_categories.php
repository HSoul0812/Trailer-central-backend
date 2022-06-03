<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentTextrailCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('textrail_categories', static function (Blueprint $table) {
            $table->integer('parent_id')
                ->after('name')
                ->nullable()
                ->comment('TexTrail category parent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('textrail_categories', static function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
}
