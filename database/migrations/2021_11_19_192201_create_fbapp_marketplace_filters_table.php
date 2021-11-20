<?php

use App\Models\Marketing\Facebook\Filter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappMarketplaceFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_marketplace_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('marketplace_id')->index();
            $table->enum('filter_type', array_keys(Filter::FILTER_TYPES));
            $table->string('filter');
            $table->timestamps();

            $table->unique(['filter_type', 'filter']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbapp_marketplace_filters');
    }
}
