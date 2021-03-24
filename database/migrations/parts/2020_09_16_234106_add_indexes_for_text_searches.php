<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIndexesForTextSearches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        DB::statement('ALTER TABLE parts_v1 ADD FULLTEXT search(title, alternative_part_number, subcategory, sku)');
        
        DB::statement('ALTER TABLE part_categories ADD FULLTEXT search(name)');
        
        DB::statement('ALTER TABLE part_types ADD FULLTEXT search(name)');
        
        DB::statement('ALTER TABLE part_manufacturers ADD FULLTEXT search(name)');
        
        DB::statement('ALTER TABLE part_brands ADD FULLTEXT search(name)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
