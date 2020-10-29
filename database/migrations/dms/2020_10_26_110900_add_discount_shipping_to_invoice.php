<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountShippingToInvoice extends Migration
{
    /**
     * Instead of adding pos sales to crm_pos_sales, we will create a new invoice for pos sales.
     * So need to add some fields to invoice table for POS Sales (sales_person_id, discount, shipping)
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->unsignedInteger('sales_person_id')->nullable()->after('repair_order_id');
            $table->decimal('discount', 10, 2)->nullable()->after('po_amount');
            $table->decimal('shipping', 10, 2)->nullable()->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->dropColumn(['sales_person_id', 'discount', 'shipping']);
        });
    }
}
