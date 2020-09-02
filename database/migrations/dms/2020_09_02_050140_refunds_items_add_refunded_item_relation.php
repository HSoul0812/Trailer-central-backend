<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefundsItemsAddRefundedItemRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_refunds_items', function (Blueprint $table) {
            // points to the item this refund is for
            $table->unsignedInteger('refunded_item_id')
                ->nullable() // nullable because intended to be compatible with old
                ->after('item_id');
            // the table where the id above is located
            $table->enum('refunded_item_tbl', [
                'crm_pos_sale_products',
                'qb_invoice_items',
            ])->nullable()
                ->after('refunded_item_id');
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
            $table->dropColumn('refunded_item_id');
            $table->dropColumn('refunded_item_tbl');
        });
    }
}
