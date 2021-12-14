<?php

namespace App\Console\Commands\CRM\Interactions;

use App\Models\CRM\Interactions\InteractionMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Class ResetInteractionMessages
 * @package App\Console\Commands\CRM\Interactions
 */
class ResetInteractionMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interaction:messages:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-import interaction messages into mysql database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('interaction_message')->delete();

        DB::table('dealer_texts_log')
            ->select('dealer_texts_log.id')
            ->join('website_lead', 'dealer_texts_log.lead_id', '=', 'website_lead.identifier')
            ->orderBy('dealer_texts_log.id')
            ->chunk(5000, function (Collection $textLogs) {
                $interactionMessage = [];
                $countOfSmsMessages = 0;

                foreach ($textLogs as $textLog) {
                    $interactionMessage[] = [
                        'tb_primary_id' => $textLog->id,
                        'message_type' => InteractionMessage::MESSAGE_TYPE_SMS,
                        'tb_name' => 'dealer_texts_log',
                        'created_at' => Carbon::now(),
                        'is_read' => true
                    ];

                    $countOfSmsMessages++;
                }

                DB::table('interaction_message')->insert($interactionMessage);

                $this->info("{$countOfSmsMessages} sms messages have been added");
            });

        DB::table('crm_email_history')
            ->select('crm_email_history.email_id')
            ->join('website_lead', 'crm_email_history.lead_id', '=', 'website_lead.identifier')
            ->orderBy('crm_email_history.email_id')
            ->chunk(5000, function (Collection $emailHistory) {
                $interactionMessage = [];
                $countOfEmailMessages = 0;

                foreach ($emailHistory as $item) {
                    $interactionMessage[] = [
                        'tb_primary_id' => $item->email_id,
                        'message_type' => InteractionMessage::MESSAGE_TYPE_SMS,
                        'tb_name' => 'crm_email_history',
                        'created_at' => Carbon::now(),
                        'is_read' => true
                    ];

                    $countOfEmailMessages++;
                }

                DB::table('interaction_message')->insert($interactionMessage);

                $this->info("{$countOfEmailMessages} email messages have been added");
            });

        DB::table('fbapp_messages')
            ->select('id')
            ->orderBy('id')
            ->chunk(5000, function (Collection $emailHistory) {
                $interactionMessage = [];
                $countOfFbMessages = 0;

                foreach ($emailHistory as $item) {
                    $interactionMessage[] = [
                        'tb_primary_id' => $item->id,
                        'message_type' => 'fb',
                        'tb_name' => 'fbapp_messages',
                        'created_at' => Carbon::now(),
                        'is_read' => true
                    ];

                    $countOfFbMessages++;
                }

                DB::table('interaction_message')->insert($interactionMessage);

                $this->info("{$countOfFbMessages} fb messages have been added");
            });
    }
}
