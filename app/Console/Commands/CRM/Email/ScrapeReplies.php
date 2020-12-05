<?php

namespace App\Console\Commands\CRM\Email;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\User\NewDealerUser;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\Email\ScrapeRepliesServiceInterface;

class ScrapeReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:scrape-replies {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scraping email replies from a sales person\'s email account for leads belonging to sales person\'s dealer.';

    /**
     * @var App\Repositories\CRM\User\SalesPersonRepositoryInterface
     */
    protected $salespeople;

    /**
     * @var App\Services\CRM\Email\ScrapeRepliesServiceInterface
     */
    protected $service;

    /**
     * @var Illuminate\Support\Facades\Redis
     */
    protected $redis;

    /**
     * @var string
     */
    protected $lkey = 'cron:email-history:sales-people';
    protected $skey = 'db:sales-people';

    /**
     * @var string
     */
    protected $command = 'email:scrape-replies';

    /**
     * @var int
     */
    protected $salesPersonId = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SalesPersonRepositoryInterface $salesRepo,
                                ScrapeRepliesServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->salespeople = $salesRepo;
        $this->redis = Redis::connection('default');

        // Get Sales Person From Predis
        try {
            $this->salesPersonId = $this->redis->lpop($this->lkey);
        } catch(\Predis\Connection\ConnectionException $e) {
            // Send Slack Error
            $this->sendSlackError($e->getMessage());
            $this->error("{$this->command} exception returned connecting to redis on scrape email replies command: " . $e->getMessage());

            // Kill Set (Invalid) Vars
            $this->salesPersonId = 0;
        }
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
            $this->command .= (!empty($dealerId) ? ' ' . $dealerId : '');
            $this->info("{$this->command} started {$now}");

            // Handle Dealer Differently
            if(!empty($dealerId)) {
                $dealers = NewDealerUser::where('id', $dealerId)->with('user')->get();
            } else {
                // Get Dealers With Active CRM
                $dealers = NewDealerUser::has('activeCrmUser')->with('user')->get();
            }
            $this->info("{$this->command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Active CRM
            foreach($dealers as $dealer) {
                // Parse Single Dealer
                $imported = $this->processDealer($dealer);
                if($imported !== false) {
                    $this->info("{$this->command} imported {$imported} emails on dealer #{$dealer->id}");
                } else {
                    $this->info("{$this->command} skipped importing emails on dealer #{$dealer->id}");
                }
            }
        } catch(\Exception $e) {
            $this->error("{$this->command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $this->info("{$this->command} finished on " . $datetime->format("l, F jS, Y"));
    }

    /**
     * Process Dealer
     * 
     * @param User $dealer
     */
    private function processDealer($dealer) {
        // Doesn't Belong to Sales Person?!
        $salesPerson = SalesPerson::find($this->salesPersonId);
        if(!empty($this->salesPersonId) && !empty($salesPerson->user_id)) {
            if($salesPerson->user_id !== $dealer->user_id) {
                return false;
            }
        }

        // Get Salespeople With Email Credentials
        $salespeople = $this->salespeople->getAllImap($dealer->user_id);
        if(count($salespeople) < 1) {
            return false;
        }

        // Loop Campaigns for Current Dealer
        $imported = 0;
        $this->info("{$this->command} dealer #{$dealer->id} found " . count($salespeople) . " active salespeople with imap credentials to process");
        foreach($salespeople as $salesperson) {
            // Not Correct Sales Person?!
            if($salesperson->id !== $this->salesPersonId) {
                continue;
            }

            // Try Catching Error for Sales Person
            try {
                // Set Current Sales Person to Redis
                $this->redis->hmset($this->skey, $salesPerson->id, json_encode($salesPerson));
                $this->salesPersonId = 0;

                // Import Emails
                $this->info("{$this->command} importing emails on sales person #{$salesperson->id} for dealer #{$dealer->id}");
                $imports = $this->service->import($dealer, $salesperson);

                // Adjust Total Import Counts
                $this->info("{$this->command} imported {$imports} emails on sales person #{$salesperson->id}");
                $imported += $imports;
            } catch(\Exception $e) {
                $this->error("{$this->command} exception returned on sales person #{$salesperson->id} {$e->getMessage()}: {$e->getTraceAsString()}");
            }
        }

        // Return Imported Email Count for Dealer
        return $imported;
    }

    /**
     * Send Slack Error
     */
    private function sendSlackError($error) {
        // Get Title/Message
        $title = "CRM EMAIL IMPORTER ERROR";
        $msg = "Exception returned trying to connect to redis client! Client returned exception: " . $error;

        // Send to Slack
        $this->slack->sendSlackNotify($msg, $title);
        return $msg;
    }
}
