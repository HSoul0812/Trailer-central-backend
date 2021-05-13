<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectorSpecificationRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collector_specification_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('collector_specification_id');
            $table->enum('condition', ['equal', 'not_equal', 'lt', 'gt', 'gte', 'lte', 'same', 'not_same', 'contains', 'not_contains']);
            $table->string('field');
            $table->string('value');
            $table->timestamps();
        });

        Schema::table('collector_specification_rules', function (Blueprint $table) {
            $table->foreign('collector_specification_id', 'collector_specification_rules_coll_spec_id_foreign')
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
        Schema::dropIfExists('collector_specification_rules');
    }
}
