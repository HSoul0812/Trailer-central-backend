<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCrmTextCampaignSubject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop CRM Text Campaign Subject
        Schema::table('crm_text_campaign', function (Blueprint $table) {
            $table->dropColumn('campaign_subject');
        });

        // Drop CRM Text Blast Subject
        Schema::table('crm_text_blast', function (Blueprint $table) {
            $table->dropColumn('campaign_subject');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Create CRM Text Campaign Subject
        Schema::create('crm_text_campaign', function (Blueprint $table) {
            $table->string('campaign_subject')->nullable();
        });

        // Create CRM Text Blast Subject
        Schema::create('crm_text_blast', function (Blueprint $table) {
            $table->string('campaign_subject')->nullable();
        });
    }
}
