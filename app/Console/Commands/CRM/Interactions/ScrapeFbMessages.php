<?php

declare(strict_types=1);

namespace App\Console\Commands\CRM\Interactions;

use Illuminate\Console\Command;
use App\Jobs\CRM\Interactions\Facebook\MessageJob;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Services\CRM\Interactions\Facebook\MessageServiceInterface;

class ScrapeFbMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:scrape-messages {boundLower?} {boundUpper?} {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scraping facebook messages from a dealer\'s facebook chat integration pages.';

    /**
     * @var App\Services\CRM\Email\ScrapeRepliesServiceInterface
     */
    protected $service;

    /**
     * @var App\Repositories\User\UserRepositoryInterface
     */
    protected $users;

    /**
     * @var App\Repositories\Integration\Facebook\PageRepositoryInterface
     */
    protected $pages;

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
                                PageRepositoryInterface $pages,
                                MessageServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->users = $users;
        $this->pages = $pages;
        
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

            $this->info("{$this->command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Valid Salespeople
            foreach($dealers as $dealer) {
                // Get Pages for Dealer
                $pages = $this->pages->getAll(['dealer_id' => $dealer->id]);
                $this->info("{$this->command} found " . count($pages) . " facebook pages #{$dealer->id} to scrape messages for");

                // Loop Pages
                foreach($pages as $page) {
                    $this->dispatch(new MessageJob($page->accessToken, $page->page_id));
                    $this->info("{$this->command} started message job for facebook page #{$page->page_id} on dealer #{$dealer->id}");
                }
            }
        } catch(\Exception $e) {
            $this->error("{$this->command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $this->info("{$this->command} finished on " . $datetime->format("l, F jS, Y H:i:s"));
    }
}
