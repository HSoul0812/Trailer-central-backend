<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDmsResourcePublicLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('dms_resource_public_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 64);
            $table->enum('resource_type', ['print_quote']);
            $table->unsignedBigInteger('resource_id')->nullable();
            
            $table->index(['token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::dropIfExists('dms_resource_public_links');
    }
}