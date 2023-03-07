<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToClappTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clapp_transaction', function (Blueprint $table) {
            $table->timestamps();

            $table->index(['dealer_id'], 'TXN_CLAPP_DEALER_ID');
            $table->index(['inventory_id'], 'TXN_CLAPP_INVENTORY_ID');
            $table->index(['session_id'], 'TXN_CLAPP_SESSION_ID');
            $table->index(['queue_id'], 'TXN_CLAPP_QUEUE_ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clapp_transaction', function (Blueprint $table) {
            $table->dropIndex('TXN_CLAPP_DEALER_ID');
            $table->dropIndex('TXN_CLAPP_INVENTORY_ID');
            $table->dropIndex('TXN_CLAPP_SESSION_ID');
            $table->dropIndex('TXN_CLAPP_QUEUE_ID');

            $table->dropTimestamps();
        });
    }
}
