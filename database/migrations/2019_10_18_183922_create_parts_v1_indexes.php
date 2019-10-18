<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsV1Indexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            
            $table->index('show_on_website');
            $table->index('is_vehicle_specific');
            
            $table->index(['show_on_website', 'dealer_id'], 'WEBSITE_LOOKUP_DEFAULT');
            $table->index(['show_on_website', 'dealer_id', 'manufacturer_id'], 'WEBSITE_LOOKUP_MFG');
            $table->index(['show_on_website', 'dealer_id', 'type_id'], 'WEBSITE_LOOKUP_TYPE');
            $table->index(['show_on_website', 'dealer_id', 'brand_id'], 'WEBSITE_LOOKUP_BRAND');
            $table->index(['show_on_website', 'dealer_id', 'category_id'], 'WEBSITE_LOOKUP_CAT');
            
            $table->index(['show_on_website', 'dealer_id', 'category_id', 'brand_id'], 'WEBSITE_LOOKUP_COMBINED_1');
            $table->index(['show_on_website', 'dealer_id', 'category_id', 'type_id'], 'WEBSITE_LOOKUP_COMBINED_2');
            
            $table->index(['show_on_website', 'dealer_id', 'brand_id', 'type_id'], 'WEBSITE_LOOKUP_COMBINED_3');
            
            $table->index(['show_on_website', 'dealer_id', 'manufacturer_id', 'type_id'], 'WEBSITE_LOOKUP_COMBINED_4');
            $table->index(['show_on_website', 'dealer_id', 'manufacturer_id', 'brand_id'], 'WEBSITE_LOOKUP_COMBINED_5');
            $table->index(['show_on_website', 'dealer_id', 'manufacturer_id', 'category_id'], 'WEBSITE_LOOKUP_COMBINED_6');
            
            $table->index(['show_on_website', 'dealer_id', 'manufacturer_id', 'type_id', 'brand_id'], 'WEBSITE_LOOKUP_COMBINED_7');
            $table->index(['show_on_website', 'dealer_id', 'manufacturer_id', 'type_id', 'category_id'], 'WEBSITE_LOOKUP_COMBINED_8');            
            $table->index(['show_on_website', 'dealer_id', 'manufacturer_id', 'brand_id', 'category_id'], 'WEBSITE_LOOKUP_COMBINED_9');
            
            $table->index(['show_on_website', 'dealer_id', 'manufacturer_id', 'type_id', 'brand_id', 'category_id'], 'WEBSITE_LOOKUP_COMBINED_10');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parts_v1');
    }
}
