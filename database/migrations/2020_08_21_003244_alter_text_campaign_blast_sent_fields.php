<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CRM\Text\CampaignSent;
use App\Models\CRM\Text\BlastSent;

class AlterTextCampaignBlastSentFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_text_campaign_sent', function (Blueprint $table) {
            if(Schema::hasColumn('crm_text_campaign_sent', 'deleted')) {
                $table->enum('status', CampaignSent::STATUS_TYPES);

                $table->dropColumn('deleted');

                $table->dropForeign('crm_text_campaign_sent_lead_id_foreign');

                //$table->dropForeign('crm_text_campaign_sent_text_id_foreign');
            }
        });

        Schema::table('crm_text_blast_sent', function (Blueprint $table) {
            if(Schema::hasColumn('crm_text_blast_sent', 'deleted')) {
                $table->enum('status', BlastSent::STATUS_TYPES);

                $table->dropColumn('deleted');

                $table->dropForeign('crm_text_blast_sent_lead_id_foreign');

                //$table->dropForeign('crm_text_blast_sent_text_id_foreign');
            }
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
