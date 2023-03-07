<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClappPostClappActivePostIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clapp_posts', function (Blueprint $table) {
            $table->index(['added']);
        });

        Schema::table('clapp_active_posts', function (Blueprint $table) {
            $table->index(['added']);
            $table->index(['updated']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clapp_posts', function (Blueprint $table) {
            $table->dropIndex(['added']);
        });

        Schema::table('clapp_active_posts', function (Blueprint $table) {
            $table->dropIndex(['added']);
            $table->dropIndex(['updated']);
        });
    }
}
