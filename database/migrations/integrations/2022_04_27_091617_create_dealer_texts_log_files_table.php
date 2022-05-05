<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerTextsLogFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_texts_log_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dealer_texts_log_id');
            $table->string('path');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::table('dealer_texts_log_files', function (Blueprint $table) {
            $table->foreign('dealer_texts_log_id')
                ->references('id')
                ->on('dealer_texts_log')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dealer_texts_log_files');
    }
}
