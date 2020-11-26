<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerCollectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('collector');

        Schema::create('collector', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dealer_id')->unsigned();
            $table->integer('dealer_location_id')->unsigned();
            $table->string('process_name', 128)->unique();

            $table->string('ftp_host', 128);
            $table->string('ftp_path', 128);
            $table->string('ftp_login', 128);
            $table->string('ftp_password', 128);

            $table->string('file_format', 16);

            $table->string('length_format', 64)->nullable();
            $table->string('width_format', 64)->nullable();
            $table->string('height_format', 64)->nullable();

            $table->boolean('show_on_rvtrader')->default(false);
            $table->string('title_format', 128)->nullable();
            $table->boolean('import_prices')->default(false);
            $table->boolean('import_description')->default(false);
            $table->string('images_delimiter', 16)->default(',');

            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('dealer_id')->references('dealer_id')->on('dealer');
        });

        exit();
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
