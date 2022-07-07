<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Dmss468BillsAddSearchIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_bills', function (Blueprint $table) {
            $table->index(['dealer_id'], 'qb_bills_dealer_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_bills', function (Blueprint $table) {
            $table->dropIndex('qb_bills_dealer_index');
        });
    }
}
