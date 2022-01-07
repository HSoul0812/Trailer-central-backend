<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceCompletedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->string('invoice_id')->index()->nullable();
            $table->string('invoice_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
            $table->dropColumn('invoice_url');
        });
    }
}
