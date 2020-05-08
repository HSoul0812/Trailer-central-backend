<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsCacheStoreTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts_cache_store_times', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->integer('dealer_id')->unsigned();
            $table->bigInteger('type_id')->unsigned()->nullable();
            $table->bigInteger('manufacturer_id')->unsigned()->nullable();
            $table->bigInteger('brand_id')->unsigned()->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->timestamp('cache_store_time');
            $table->timestamp('update_time');
            
            $table->foreign('dealer_id')
                    ->references('dealer_id')
                    ->on('dealer');
            
            $table->foreign('type_id')
                    ->references('id')
                    ->on('part_types');
            
            $table->foreign('manufacturer_id')
                    ->references('id')
                    ->on('part_manufacturers');
            
            $table->foreign('brand_id')
                    ->references('id')
                    ->on('part_brands');
            
            $table->foreign('category_id')
                    ->references('id')
                    ->on('part_categories');
            
            $table->index(['dealer_id', 'type_id'], 'CACHE_LOOKUP_TYPE');
            $table->index(['dealer_id', 'manufacturer_id'], 'CACHE_LOOKUP_MFG');
            $table->index(['dealer_id', 'brand_id'], 'CACHE_LOOKUP_BRAND');
            $table->index(['dealer_id', 'category_id'], 'CACHE_LOOKUP_CATEGORY');
            
            $table->index(['dealer_id', 'type_id', 'manufacturer_id'], 'CACHE_LOOKUP_COMBINED_1');
            $table->index(['dealer_id', 'type_id', 'brand_id'], 'CACHE_LOOKUP_COMBINED_2');
            $table->index(['dealer_id', 'type_id', 'category_id'], 'CACHE_LOOKUP_COMBINED_3');
            
            $table->index(['dealer_id', 'brand_id', 'manufacturer_id'], 'CACHE_LOOKUP_COMBINED_4');
            $table->index(['dealer_id', 'brand_id', 'category_id'], 'CACHE_LOOKUP_COMBINED_5');
            
            $table->index(['dealer_id', 'manufacturer_id', 'category_id'], 'CACHE_LOOKUP_COMBINED_6');
            
            $table->index(['dealer_id', 'manufacturer_id', 'category_id', 'brand_id'], 'CACHE_LOOKUP_COMBINED_7');
            $table->index(['dealer_id', 'manufacturer_id', 'category_id', 'type_id'], 'CACHE_LOOKUP_COMBINED_8');
            
            $table->index(['dealer_id', 'manufacturer_id', 'category_id', 'type_id', 'brand_id'], 'CACHE_LOOKUP_COMBINED_9');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parts_cache_store_times');
    }
}
