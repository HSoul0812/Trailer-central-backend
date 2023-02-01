<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectorLogTable extends Migration
{
    public const TABLE = "collector_log";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('collector_id');
            $table->foreign('collector_id')
                ->references('id')
                ->on('collector')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');

            $table->mediumText('new_items')->nullable();
            $table->mediumText('sold_items')->nullable();
            $table->mediumText('archived_items')->nullable();
            $table->mediumText('unarchived_items')->nullable();

            $table->longText('validation_errors')->nullable();
            $table->text('exception')->nullable();

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
        Schema::dropIfExists(self::TABLE);
    }
}
