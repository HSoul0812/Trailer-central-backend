<?php

use App\Models\CRM\Leads\LeadType;
use Illuminate\Database\Migrations\Migration;

class AddNonLeadFacebookToWebsiteLeadType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update Website Lead > Lead Type to Support NonLead / Facebook
        DB::statement("ALTER TABLE website_lead MODIFY COLUMN lead_type ENUM('" . implode("', '", LeadType::TYPE_ARRAY_FULL) . "')");
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
