<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxAmountPerQbInvoiceItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('qb_invoice_items', function (Blueprint $table) {
            // this column have to be nullable since we must identify when a record on "qb_invoice_items" could be have in account
            // so:
            //      a) null -> means that record was tax-untraceable in the past
            //      b) zero -> means that record had no taxed
            $table->decimal('taxes_amount', 9, 2)
                ->unsigned()
                ->nullable()
                ->after('is_taxable')
                ->comment('When: a) null -> means that record was tax-untraceable in the past b) zero -> means that record had no taxes');

            // the above column makes deprecated the column "is_taxable", but we need to keep that column for backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('qb_invoice_items', function (Blueprint $table) {
            $table->dropColumn('taxes_amount');
        });
    }
}
