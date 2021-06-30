<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsCostHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts_cost_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('part_id')->unsigned();
            $table->decimal('old_cost', 9, 2)->unsigned();
            $table->decimal('new_cost', 9, 2)->unsigned();
            $table->integer('expense_id')->nullable();
            $table->timestamps();
        });

        Schema::table('parts_cost_history', function (Blueprint $table) {
            $table->foreign('part_id')
                ->references('id')
                ->on('parts_v1')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('expense_id')
                ->references('id')
                ->on('qb_expenses')
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
        Schema::dropIfExists('parts_cost_history');
    }
}
