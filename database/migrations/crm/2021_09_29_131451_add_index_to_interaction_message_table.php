<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToInteractionMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interaction_message', function (Blueprint $table) {
            $table->index(['tb_primary_id', 'tb_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interaction_message', function (Blueprint $table) {
            $table->dropIndex(['tb_primary_id', 'tb_name']);
        });
    }
}
