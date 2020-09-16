<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternalInvoiceNum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            // If invoice doc num is duplicated on QBO, a dealer should set this field to send the invoice to QBO.
            $table->string('qb_doc_num')->nullable()->after('memo');
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
            $table->dropColumn('qb_doc_num');
        });
    }
}
