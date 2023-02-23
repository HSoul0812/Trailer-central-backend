<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToWebsiteLeadTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_lead_trades', function (Blueprint $table) {
            $table->index('lead_id', 'LEAD_ID_SOLO');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_lead_trades', function (Blueprint $table) {
            $table->dropIndex('LEAD_ID_SOLO');
        });
    }
}
