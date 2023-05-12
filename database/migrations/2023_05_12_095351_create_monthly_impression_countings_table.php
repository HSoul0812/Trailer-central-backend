<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlyImpressionCountingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('monthly_impression_countings', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->integer('dealer_id');
            $table->integer('impressions_count')->comment('This is the total count of PLP count for all inventory_id.');
            $table->integer('views_count')->comment('This is the total count of Dealer Page and PDP count for all inventory_id.');
            $table->string('zip_file_path');
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->unique(['year', 'month', 'dealer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('monthly_impression_countings');
    }
}
