<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AddRowsToInteractionMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('interaction_message')->delete();

        DB::table('dealer_texts_log')
            ->select('dealer_texts_log.id')
            ->join('website_lead', 'dealer_texts_log.lead_id', '=', 'website_lead.identifier')
            ->orderBy('dealer_texts_log.id')
            ->chunk(5000, function (Collection $textLogs) {
                $interactionMessage = [];

                foreach ($textLogs as $textLog) {
                    array_push($interactionMessage, [
                        'tb_primary_id' => $textLog->id,
                        'message_type' => 'sms',
                        'tb_name' => 'dealer_texts_log',
                        'created_at' => Carbon::now(),
                    ]);
                }

                DB::table('interaction_message')->insert($interactionMessage);
            });

        DB::table('crm_email_history')
            ->select('crm_email_history.email_id')
            ->join('website_lead', 'crm_email_history.lead_id', '=', 'website_lead.identifier')
            ->orderBy('crm_email_history.email_id')
            ->chunk(5000, function (Collection $emailHistory) {
                $interactionMessage = [];

                foreach ($emailHistory as $item) {
                    array_push($interactionMessage, [
                        'tb_primary_id' => $item->email_id,
                        'message_type' => 'email',
                        'tb_name' => 'crm_email_history',
                        'created_at' => Carbon::now(),
                    ]);
                }

                DB::table('interaction_message')->insert($interactionMessage);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('interaction_message')->delete();
    }
}
