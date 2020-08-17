<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCrmLeadAssignIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_lead_assign', function (Blueprint $table) {
            $table->index('dealer_id');
            $table->index('lead_id');

            $table->index(['dealer_id', 'lead_id'], 'DEALER_LEAD_LOOKUP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
