<?php

namespace App\Console\Commands\CRM\Email;

use Illuminate\Console\Command;
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
            $command = "email:scrape-replies" . (!empty($dealerId) ? ' ' . $dealerId : '');
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
                // Get Salespeople With Email Credentials
                $salespeople = $this->salespeople->getAllImap($dealer->user_id);
                if(count($salespeople) < 1) {
                    continue;
                }

                // Loop Campaigns for Current Dealer
                $this->info("{$command} dealer #{$dealer->id} found " . count($salespeople) . " active salespeople with imap credentials to process");
                foreach($salespeople as $salesperson) {
                    // Try Catching Error for Sales Person
                    try {
                        // Import Emails
                        $this->service->import($dealer, $salesperson);
                    } catch(\Exception $e) {
                        $this->error("{$command} exception returned on sales person #{$salesperson->id} {$e->getMessage()}: {$e->getTraceAsString()}");
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
