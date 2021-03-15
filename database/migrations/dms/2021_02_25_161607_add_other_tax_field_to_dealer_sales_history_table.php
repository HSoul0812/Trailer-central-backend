<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherTaxFieldToDealerSalesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_sales_history', function (Blueprint $table) {
            $table->decimal('other_tax', 10)->default(0.00)->after('district4_tax');
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
            $table->dropColumn('other_tax');
        });
    }
}
