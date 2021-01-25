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
            $table->string('email', 50)->index();
            $table->timestamps();

            $table->unique(['dealer_id', 'email']);
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
