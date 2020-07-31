<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBilledToPoReceipt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_purchase_order_receipt', function (Blueprint $table) {
            $table->tinyInteger('is_billed')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_purchase_order_receipt', function (Blueprint $table) {
            $table->dropColumn('is_billed');
        });
    }
}
