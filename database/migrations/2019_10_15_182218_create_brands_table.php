<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('part_brands')) {
            Schema::create('part_brands', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 50)->unique();
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
        Schema::dropIfExists('part_brands');
    }
}
