<?php

namespace App\Console\Commands\CRM\Email;

use Illuminate\Console\Command;
use App\Models\CRM\User\SalesPerson;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Email\ScrapeRepliesServiceInterface;

class ScrapeReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:scrape-replies {boundLower?} {boundUpper?} {dealer?}';

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
     * @var App\Repositories\User\UserRepositoryInterface
     */
    protected $users;

    /**
     * @var string
     */
    protected $command = '';

    /**
     * @var int
     */
    protected $dealerId = 0;
    protected $salesPersonId = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SalesPersonRepositoryInterface $salesRepo,
                                UserRepositoryInterface $users,
                                ScrapeRepliesServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->salespeople = $salesRepo;
        $this->users = $users;
        
        date_default_timezone_set(env('DB_TIMEZONE'));
        
        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Dealer ID
        $this->dealerId = $this->argument('dealer');
        
        $this->boundLower = $this->argument('boundLower');
        $this->boundUpper = $this->argument('boundUpper');        
        
        $now = $this->datetime->format("l, F jS, Y");
        $this->command = str_replace('{dealer?}', $this->dealerId, $this->signature);

        // Try Catching Error for Whole Script
        try {
            $this->info("{$this->command} started {$now}");

            $dealers = $this->users->getCrmActiveUsers([
                'bound_lower' => $this->boundLower,
                'bound_upper' => $this->boundUpper,
                'dealer_id' => $this->dealerId
            ]);

            $this->info("{$this->command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Valid Salespeople
            foreach($dealers as $dealer) {
                // Parse Single Dealer
                $imported = $this->processDealer($dealer);
                if($imported !== false) {
                    $this->info("{$this->command} imported {$imported} emails on dealer #{$dealer->id}");
                } else {
                    $this->info("{$this->command} skipped importing emails on dealer #{$dealer->id}");
                }
                unset($dealer);
            }
        } catch(\Exception $e) {
            $this->error("{$this->command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
        }
        unset($dealers);

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
                unset($salesPerson);
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
            if(!empty($this->salesPersonId) && $salesperson->id !== $this->salesPersonId) {
                // Clear Memory
                unset($salesperson);
                continue;
            }
            $this->salesPersonId = 0;

            // Try Catching Error for Sales Person
            try {
                // Import Emails
                $this->info("{$this->command} importing emails on sales person #{$salesperson->id} for dealer #{$dealer->id}");
                $imports = $this->processSalesperson($dealer, $salesperson);

                // Adjust Total Import Counts
                $this->info("{$this->command} imported {$imports} emails on sales person #{$salesperson->id}");
                $imported += $imports;
                unset($imports);
            } catch(\Exception $e) {
                $this->error("{$this->command} exception returned on sales person #{$salesperson->id} {$e->getMessage()}: {$e->getTraceAsString()}");
            }

            // Clear Memory
            unset($salesperson);
        }

        // Return Imported Email Count for Dealer
        return $imported;
    }

    /**
     * Process Sales Person
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return false || array of EmailHistory
     */
    private function processSalesperson($dealer, $salesperson) {
        // Process Messages
        $this->info($this->command . ' processing getting emails for sales serson #' . $salesperson->id);
        $imported = 0;
        foreach($salesperson->email_folders as $folder) {
            // Try Catching Error for Sales Person Folder
            try {
                // Import Folder
                $imports = $this->service->import($dealer, $salesperson, $folder);
                $this->info($this->command . ' imported ' . $imports . ' email replies for sales person #' .
                            $salesperson->id . ' folder ' . $folder->name);
                $imported += $imports;
            } catch(\Exception $e) {
                $this->error($this->command . ' error importing sales person #' .
                            $salesperson->id . ' folder ' . $folder->name . '; ' .
                            $e->getMessage() . ':' . $e->getTraceAsString());
            }
        }

        // Return Campaign Sent Entries
        return $imported;
    }
}
