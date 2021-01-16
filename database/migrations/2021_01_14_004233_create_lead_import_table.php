<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadImportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_import', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dealer_id')->index();
            $table->integer('dealer_location_id')->index();
            $table->text('email')->index();
            $table->timestamps();

            $table->index(['dealer_id', 'dealer_location_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_import');
    }
}
