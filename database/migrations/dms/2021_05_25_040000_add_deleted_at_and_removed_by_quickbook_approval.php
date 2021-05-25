<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtAndRemovedByQuickbookApproval extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbook_approval', function(Blueprint $table)
        {
            $table->string('removed_by', 255);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quickbook_approval', function(Blueprint $table)
        {
            $table->dropColumn('removed_by');
            $table->dropSoftDeletes();
        });
    }
}
