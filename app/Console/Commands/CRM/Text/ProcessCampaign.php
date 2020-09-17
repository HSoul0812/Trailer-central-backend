<?php

namespace App\Console\Commands\CRM\Text;

use Illuminate\Console\Command;
use App\Models\User\NewDealerUser;
use App\Services\CRM\Text\CampaignServiceInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use Carbon\Carbon;

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
     * @var App\Services\CRM\Text\CampaignServiceInterface
     */
    protected $service;

    /**
     * @var App\Repositories\CRM\Text\TextRepository
     */
    protected $texts;

    /**
     * @var App\Repositories\CRM\Text\CampaignRepository
     */
    protected $campaigns;

    /**
     * @var datetime
     */
    protected $datetime = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CampaignServiceInterface $service,
                                TextRepositoryInterface $textRepo,
                                CampaignRepositoryInterface $campaignRepo)
    {
        parent::__construct();

        $this->service = $service;
        $this->texts = $textRepo;
        $this->campaigns = $campaignRepo;
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
            $command = "text:process-campaign" . (!empty($dealerId) ? ' ' . $dealerId : '');
            $this->info("{$command} started {$now}");

            // Handle Dealer Differently
            if(!empty($dealerId)) {
                $dealers = NewDealerUser::where('id', $dealerId)->with('user')->get();
            } else {
                $dealers = NewDealerUser::has('activeCrmUser')->with('user')->get();
            }
            $this->info("{$command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Active CRM
            foreach($dealers as $dealer) {
                // Get Campaigns for Dealer
                $campaigns = $this->campaigns->getAllActive($dealer->user_id);
                if(count($campaigns) < 1) {
                    continue;
                }

                // Loop Campaigns for Current Dealer
                $this->info("{$command} dealer #{$dealer->id} found " . count($campaigns) . " active campaigns to process");
                foreach($campaigns as $campaign) {
                    // Try Catching Error for Campaign
                    try {
                        // Send Campaign
                        $this->service->send($dealer, $campaign);
                    } catch(\Exception $e) {
                        $this->error("{$command} exception returned on campaign #{$campaign->id} {$e->getMessage()}: {$e->getTraceAsString()}");
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
