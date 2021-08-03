<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->index(['doc_num'], 'qb_invoices_doc_num_index_lookup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->dropIndex('qb_invoices_doc_num_index_lookup');
        });
    }
}
