<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteEntityCssesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('website_entity_css', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('website_entity_id');
            $table->string('name');
            $table->text('content');
            $table->integer('sort_order')->nullable();
            $table->foreign('website_entity_id')->references('id')->on('website_entity')->onDelete('cascade');
            $table->timestamps();
        });

        //hack to change website_entity_id to length 255 to match base table
        DB::statement("alter table website_entity_css modify column website_entity_id int(255) not null");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('website_entity_css');
    }
}
