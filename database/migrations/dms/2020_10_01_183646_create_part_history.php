<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts_audit_log', function (Blueprint $table) {
            //
            $table->bigIncrements('id');
            $table->unsignedInteger('part_id');
            $table->unsignedInteger('bin_id');
            $table->integer('qty');
            $table->integer('balance');
            $table->string('description');
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
        Schema::drop('parts_audit_log');
    }
}
