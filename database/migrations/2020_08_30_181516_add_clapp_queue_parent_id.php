<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClappQueueParentId extends Migration
{
    // Define Index Names to Be Added
    const PARENT_ID_INDEX = 'CLAPP_QUEUE_PARENT_ID_COMMAND_STATUS';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clapp_queue', function (Blueprint $table) {
            // Add Parent ID to Clapp Queue
            $table->integer('parent_id')->nullable()->after('session_id');
            
            // Set Command Length to 20
            $table->string('command', 20)->change();

            // Add Indexes
            $table->index(['parent_id', 'command', 'status'], self::PARENT_ID_INDEX);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clapp_queue', function (Blueprint $table) {
            // Drop Index for Parent ID
            $table->dropIndex(self::PARENT_ID_INDEX);

            // Drop Parent ID from Clapp Queue
            $table->dropColumn('parent_id');
        });
    }
}
