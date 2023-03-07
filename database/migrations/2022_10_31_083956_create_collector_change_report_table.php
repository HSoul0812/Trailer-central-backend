<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectorChangeReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collector_change_report', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('collector_id');
            $table->integer('user_id');

            $table->string('field');
            $table->text('changed_from')->nullable();
            $table->text('changed_to')->nullable();

            $table->foreign('collector_id')
                ->references('id')
                ->on('collector')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collector_change_report');
    }
}
