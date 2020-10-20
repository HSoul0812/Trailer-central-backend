<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InvoicesAddPoAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            //
            $table->decimal('po_amount', 10, 2)
                ->default(0)
                ->after('po_no');
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
            //
            $table->dropColumn('po_amount');
        });
    }
}
