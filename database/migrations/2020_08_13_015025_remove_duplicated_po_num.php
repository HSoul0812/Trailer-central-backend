<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveDuplicatedPoNum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_purchase_order', function (Blueprint $table) {
            $table->unique(['dealer_id', 'user_defined_id'], 'PO_NUM_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_purchase_order', function (Blueprint $table) {
            $table->dropUnique('PO_NUM_UNIQUE');
        });
    }

}
