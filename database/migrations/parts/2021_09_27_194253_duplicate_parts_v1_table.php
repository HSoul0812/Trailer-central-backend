<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DuplicatePartsV1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::transaction(function() {
            DB::statement('CREATE TABLE textrail_parts LIKE parts_v1');
            DB::statement('CREATE TABLE textrail_brands LIKE part_brands');
            DB::statement('CREATE TABLE textrail_manufacturers LIKE part_manufacturers');
            DB::statement('CREATE TABLE textrail_categories LIKE part_categories');
            DB::statement('CREATE TABLE textrail_types LIKE part_types');
            DB::statement('CREATE TABLE textrail_images LIKE part_images');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::transaction(function() {
            DB::statement('DROP TABLE textrail_parts');
            DB::statement('DROP TABLE textrail_brands');
            DB::statement('DROP TABLE textrail_manufacturers');
            DB::statement('DROP TABLE textrail_categories');
            DB::statement('DROP TABLE textrail_types');
            DB::statement('DROP TABLE textrail_images');            
        });
    }
}
