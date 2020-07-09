<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceOrderIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            $table->index('dealer_id', 'DEALER_ID');
            $table->index('user_defined_id', 'USER_DEFINED_ID');
            $table->index('total_price', 'TOTAL_PRICE');
            $table->index(['dealer_id', 'type', 'status'], 'STATUS_LOOKUP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            $table->dropIndex('DEALER_ID');
            $table->dropIndex('USER_DEFINED_ID');
            $table->dropIndex('TOTAL_PRICE');
            $table->dropIndex('STATUS_LOOKUP');
        });
    }
}
