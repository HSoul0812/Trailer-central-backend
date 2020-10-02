<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerIdDateSubmittedIndexToWebsiteLead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->index(['date_submitted', 'dealer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->dropIndex(['date_submitted', 'dealer_id']);
        });
    }
}
