<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQbInvoiceItemInventoriesDataSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('qb_invoice_item_inventories', function (Blueprint $table) {
            $table->integer('invoice_item_id')->primary();
            $table->integer('inventory_id')->unsigned()->index('qb_invoice_item_inventories_inventory_id');

            $table->decimal('cost_overhead', 9, 2)->unsigned()->default(0.0);

            $table->decimal('true_total_cost', 9, 2)->unsigned()->default(0.0);

            $table->foreign('invoice_item_id')
                ->references('id')
                ->on('qb_invoice_items')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('qb_invoice_item_inventories');
    }
}
