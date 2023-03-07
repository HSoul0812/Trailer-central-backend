<?php

declare(strict_types=1);

namespace App\Console\Commands\CRM\Interactions;

use App\Jobs\CRM\Interactions\Facebook\MessageJob;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Integration\Facebook\ChatRepositoryInterface;
use App\Services\CRM\Interactions\Facebook\MessageServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ScrapeFbMessages extends Command
{
    use DispatchesJobs;

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
     * @var App\Repositories\Integration\Facebook\ChatRepositoryInterface
     */
    protected $chats;

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
                                ChatRepositoryInterface $chats,
                                MessageServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->users = $users;
        $this->chats = $chats;

        date_default_timezone_set(config('app.db_timezone'));

        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(config('app.db_timezone')));
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
                $chats = $this->chats->getAll(['user_id' => $dealer->user_id]);
                $this->info("{$this->command} found " . count($chats) . " facebook pages on dealer #{$dealer->id} to scrape messages for");

                // Loop Chats
                foreach($chats as $chat) {
                    try {
			if(empty($chat->page->accessToken)) {
                            throw new \Exception('Missing Page Access Token, Cannot Start MessageJob');
			}
                        $job = new MessageJob($chat->page->accessToken, $chat->page_id);
                        $this->dispatch($job->onQueue('fb-messenger'));
                        $this->info("{$this->command} started message job for facebook page #{$chat->page_id} on dealer #{$dealer->id}");
                    } catch (\Exception $ex) {
                        $this->error("{$this->command} message job for facebook page #{$chat->page_id} on dealer #{$dealer->id} returned exception {$ex->getMessage()}");
                    }
                }
            }
        } catch(\Exception $e) {
            $this->error("{$this->command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(config('app.db_timezone')));
        $this->info("{$this->command} finished on " . $datetime->format("l, F jS, Y H:i:s"));
    }
}
