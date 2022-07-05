<?php

declare(strict_types=1);

namespace App\Console\Commands\CRM\Email;

use Illuminate\Console\Command;
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
    protected $boundLower = 0;
    protected $boundUpper = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepositoryInterface $users,
                                ScrapeRepliesServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->users = $users;

        date_default_timezone_set(env('DB_TIMEZONE'));

        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get Dealer ID
        $this->dealerId = $this->argument('dealer');

        $this->boundLower = $this->argument('boundLower');
        $this->boundUpper = $this->argument('boundUpper');

        $now = $this->datetime->format("l, F jS, Y H:i:s");
        $this->command = str_replace(['{boundLower?}', '{boundUpper?}', '{dealer?}'], [$this->boundLower, $this->boundUpper, $this->dealerId], $this->signature);

        // Try Catching Error for Whole Script
        try {
            $this->info("{$this->command} started {$now}");

            $dealers = $this->users->getCrmActiveUsers([
                'bound_lower' => $this->boundLower,
                'bound_upper' => $this->boundUpper,
                'dealer_id' => $this->dealerId
            ]);

            $this->info($this->command . ' found ' . $dealers->count() . ' dealers to process');

            // Get Dealers With Valid Salespeople
            foreach($dealers as $dealer) {
                // Parse Single Dealer
                $imported = $this->service->dealer($dealer);
                if($imported !== false) {
                    $this->info($this->command . ' imported ' . $imported . ' emails on dealer #' . $dealer->id);
                } else {
                    $this->info($this->command . ' skipped importing emails on dealer #' . $dealer->id);
                }
            }
        } catch(\Exception $e) {
            $this->error($this->command . ' exception returned ' . $e->getMessage());
        }

        // Sleep for a Second to Prevent Rate Limiting
        sleep(1);

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $this->info("{$this->command} finished on " . $datetime->format("l, F jS, Y H:i:s"));
    }
}
