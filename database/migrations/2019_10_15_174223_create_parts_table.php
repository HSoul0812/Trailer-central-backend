<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('parts_v1')) {
            Schema::create('parts_v1', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('dealer_id');
                $table->integer('vendor_id')->nullable();
                $table->integer('vehicle_specific_id')->nullable();
                $table->integer('manufacturer_id');
                $table->integer('brand_id');
                $table->integer('type_id');
                $table->integer('category_id');
                $table->integer('qb_id')->nullable();
                $table->string('subcategory');
                $table->string('title')->nullable();
                $table->string('sku');
                $table->decimal('price', 9, 2);
                $table->decimal('dealer_cost', 9, 2)->nullable();
                $table->decimal('msrp', 9, 2)->nullable();
                $table->double('weight', 14, 6)->nullable();
                $table->double('weight_rating', 14, 6)->nullable();
                $table->text('description')->nullable();
                $table->integer('qty')->nullable();
                $table->boolean('show_on_website')->default(false);
                $table->boolean('is_vehicle_specific')->default(false);            
                $table->timestamps();
            });
        }
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
