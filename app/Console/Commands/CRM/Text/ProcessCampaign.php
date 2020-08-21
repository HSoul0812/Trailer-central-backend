<?php

namespace App\Console\Commands\CRM\Text;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Exceptions\CRM\Text\NoDealerSmsNumberAvailableException;
use App\Models\User\NewDealerUser;
use App\Services\CRM\Text\TextServiceInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;

class ProcessCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'text:process-campaign {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process sending texts to all leads on all active campaigns.';

    /**
     * @var App\Services\CRM\Text\TextServiceInterface
     */
    protected $service;

    /**
     * @var App\Repositories\CRM\Leads\LeadRepository
     */
    protected $leads;

    /**
     * @var App\Repositories\CRM\Text\TextRepository
     */
    protected $texts;

    /**
     * @var App\Repositories\CRM\Text\CampaignRepository
     */
    protected $campaigns;

    /**
     * @var App\Repositories\CRM\Text\TemplateRepository
     */
    protected $templates;

    /**
     * @var App\Repositories\User\DealerLocationRepository
     */
    protected $dealerLocation;

    /**
     * @var datetime
     */
    protected $datetime = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TextServiceInterface $service, LeadRepositoryInterface $leadRepo,
                                CampaignRepositoryInterface $campaignRepo, TemplateRepositoryInterface $templateRepo,
                                TextRepositoryInterface $textRepo, DealerLocationRepositoryInterface $dealerLocationRepo)
    {
        parent::__construct();

        $this->service = $service;
        $this->leads = $leadRepo;
        $this->texts = $textRepo;
        $this->campaigns = $campaignRepo;
        $this->templates = $templateRepo;
        $this->dealerLocation = $dealerLocationRepo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Dealer ID
        $dealerId = $this->argument('dealer');

        // Initialize Time
        date_default_timezone_set(env('DB_TIMEZONE'));
        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));

        // Try Catching Error for Whole Script
        try {
            // Log Start
            $now = $this->datetime->format("l, F jS, Y");
            $command = "text:campaign" . (!empty($dealerId) ? ' ' . $dealerId : '');
            $this->info("{$command} started {$now}");

            // Handle Dealer Differently
            $dealers = array();
            if(!empty($dealerId)) {
                $dealer = NewDealerUser::findOrFail($dealerId);
                $dealers[] = $dealer;
            } else {
                $dealers = NewDealerUser::has('activeCrmUser')->has('salespeopleEmails')->get();
            }
            $this->info("{$command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Valid Salespeople
            foreach($dealers as $dealer) {
                // Get Unassigned Leads
                $campaigns = $this->campaigns->getAll([
                    'is_enabled' => true,
                    'per_page' => 'all',
                    'user_id' => $dealer->user_id
                ]);
                if(count($campaigns) < 1) {
                    continue;
                }

                // Loop Campaigns for Current Dealer
                $this->info("{$command} dealer #{$dealer->id} found " . count($campaigns) . " active campaigns to process");
                foreach($campaigns as $campaign) {
                    // Get From Number
                    $from_number = $campaign->from_sms_number;
                    if(empty($from_number)) {
                        $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                        if(empty($from_number)) {
                            continue;
                        }
                    }

                    // Get Unsent Campaign Leads
                    $leads = $this->campaigns->getLeads([
                        'per_page' => 'all',
                        'id' => $campaign->id
                    ]);
                    if(count($leads) < 1) {
                        continue;
                    }

                    // Get Template!
                    $template = $campaign->template->template;

                    // Loop Leads for Current Dealer
                    $this->info("{$command} dealer #{$dealer->id} campaign {$campaign->campaign_name} found " . count($leads) . " leads to process");
                    foreach($leads as $lead) {
                        // Initialize Notes Array
                        $leadName = $lead->id_name;

                        // Get To Numbers
                        $to_number = $lead->text_phone;
                        if(empty($to_number)) {
                            continue;
                        }
                        $to_number = "+12626619236"; // DEBUG OVERRIDE

                        // Get Text Message
                        $textMessage = $this->templates->fillTemplate($template, [
                            'lead_name' => $lead->full_name,
                            'title_of_unit_of_interest' => $lead->inventory->title,
                            'dealer_name' => $dealer->name
                        ]);
                        $this->info("{$command} preparing to send text to {$leadName} at {$to_number}");

                        // Send Text
                        $this->service->send($from_number, $to_number, $textMessage, $lead->full_name);
                        $this->info("{$command} send text to {$leadName} at {$to_number}");

                        // If ANY Errors Occur, Make Sure Text Still Gets Marked Sent!
                        try {
                            // Save Lead Status
                            $this->texts->updateLeadStatus($lead);
                            $this->info("{$command} updated lead {$leadName} status");

                            // Log SMS
                            $textLog = $this->texts->create([
                                'lead_id'     => $leadId,
                                'from_number' => $from_number,
                                'to_number'   => $to_number,
                                'log_message' => $textMessage
                            ]);
                            $this->info("{$command} logged text for {$leadName} at {$to_number}");
                        } catch(\Exception $e) {
                            $this->error("{$command} exception returned after campaign sent {$e->getMessage()}: {$e->getTraceAsString()}");
                        }

                        // Mark Campaign as Sent to Lead
                        $this->campaigns->sent([
                            'text_campaign_id' => $campaign->id,
                            'lead_id' => $lead->identifier,
                            'text_id' => !empty($textLog->id) ? $textLog->id : 0
                        ]);
                        $this->info("{$command} inserted campaign sent for lead {$leadName}");
                        die;
                    }
                }
            }
        } catch(\Exception $e) {
            $this->error("{$command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $this->info("{$command} finished on " . $datetime->format("l, F jS, Y"));
    }
}
