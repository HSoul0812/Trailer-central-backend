<?php

use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\Blast;
use Illuminate\Database\Migrations\Migration;

class AddNewCustomerActionsToCampaign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update Customer Actions for Drip Campaigns
        DB::statement("ALTER TABLE crm_drip_campaigns MODIFY COLUMN action ENUM('" . implode("', '", Campaign::STATUS_ACTIONS) . "')");

        // Update Customer Actions for Email Blast
        DB::statement("ALTER TABLE crm_email_blasts MODIFY COLUMN action ENUM('" . implode("', '", Blast::STATUS_ACTIONS) . "')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
