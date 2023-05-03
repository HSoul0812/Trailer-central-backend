<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUrlFieldOfSubscribeEmailSearchTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('subscribe_email_search', function (Blueprint $table) {
            $table->string('url', 2048)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('subscribe_email_search', function (Blueprint $table) {
            $table->string('url')->change();
        });
    }
}
