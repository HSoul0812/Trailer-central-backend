<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddErrorResultToQuickbookApproval extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbook_approval', function (Blueprint $table) {
            $table->text('error_result')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quickbook_approval', function (Blueprint $table) {
            $table->dropColumn('error_result');
        });        
    }
}
