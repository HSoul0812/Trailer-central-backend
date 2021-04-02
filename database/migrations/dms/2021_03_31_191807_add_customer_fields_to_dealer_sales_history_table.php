<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerFieldsToDealerSalesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_sales_history', function (Blueprint $table) {
            $table->string('customer_state')->nullable()->after('city');
            $table->string('customer_county')->nullable()->after('customer_state');
            $table->string('customer_city')->nullable()->after('customer_county');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_sales_history', function (Blueprint $table) {
            $table->dropColumn('customer_state');
            $table->dropColumn('customer_county');
            $table->dropColumn('customer_city');
        });
    }
}
