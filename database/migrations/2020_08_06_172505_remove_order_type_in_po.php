<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOrderTypeInPo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_purchase_order', function (Blueprint $table) {
            $table->dropColumn('order_type');
            $table->dropColumn('department');
            $table->dropColumn('ship_terms');
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
            $table->string('order_type');
            $table->string('department')->nullable();
            $table->string('ship_terms')->nullable();
        });
    }
}
