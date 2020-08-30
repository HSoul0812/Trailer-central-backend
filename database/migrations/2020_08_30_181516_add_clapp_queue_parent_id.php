<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClappQueueParentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clapp_queue', function (Blueprint $table) {
            // Add Parent ID to Clapp Queue
            $table->integer('parent_id', 10)->nullable()->after('session_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clapp_queue', function (Blueprint $table) {
            // Add Parent ID to Clapp Queue
            $table->dropColumn('parent_id');
        });
    }
}
