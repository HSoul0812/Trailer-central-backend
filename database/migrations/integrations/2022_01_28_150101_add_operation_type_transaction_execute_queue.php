<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Feed\TransactionExecuteQueue;

class AddOperationTypeTransactionExecuteQueue extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('transaction_execute_queue', function (Blueprint $table) {
            $table->enum('operation_type', [TransactionExecuteQueue::INSERT_OPERATION_TYPE, TransactionExecuteQueue::UPDATE_OPERATION_TYPE])->default(TransactionExecuteQueue::INSERT_OPERATION_TYPE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('transaction_execute_queue', function (Blueprint $table) {
            $table->dropColumn('operation_type');
        });
    }


}
