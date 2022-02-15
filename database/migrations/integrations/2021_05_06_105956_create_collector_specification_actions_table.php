<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectorSpecificationActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collector_specification_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('collector_specification_id');
            $table->enum('action', ['mapping', 'skip_item']);
            $table->string('field')->nullable();
            $table->string('value')->nullable();
            $table->timestamps();
        });

        Schema::table('collector_specification_actions', function (Blueprint $table) {
            $table->foreign('collector_specification_id', 'collector_specification_actions_coll_spec_id_foreign')
                ->references('id')
                ->on('collector_specification')
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
        Schema::dropIfExists('collector_specification_actions');
    }
}
