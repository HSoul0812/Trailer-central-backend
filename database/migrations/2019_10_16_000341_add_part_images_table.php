<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('part_images')) {
            Schema::create('part_images', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('part_id')->unsigned();
                $table->string('image_url');
                $table->timestamps();
            });
        }

//        Schema::table('part_images', function (Blueprint $table) {
//            $table->foreign('part_id')
//                    ->references('id')
//                    ->on('parts_v1')
//                    ->onDelete('CASCADE')
//                    ->onUpdate('CASCADE');
//        });

//
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
