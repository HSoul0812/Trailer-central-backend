<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefundsAddQtyReturned extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_refunds_items', function (Blueprint $table) {
            //
            $table->integer('quantity')->default(0)->nullable()->after('item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_refunds_items', function (Blueprint $table) {
            //
            $table->dropColumn('quantity');
        });
    }
}
