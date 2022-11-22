<?php

namespace App\Console\Commands\Marketing\Craigslist;

use App\Repositories\Marketing\Craigslist\PosterRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class ValidateExtensionRunning
 * 
 * @package App\Console\Commands\Marketing\Craigslist
 */
class ValidateExtensionRunning extends Command
{
    /**
     * @var Log
     */
    private $slack;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketing:craigslist:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate Craigslist Extension is still active.';

    /**
     * Create a new command instance.
     *
     * @param MarketplaceRepositoryInterface $repo
     * @return void
     */
    public function __construct(PosterRepositoryInterface $repo)
    {
        parent::__construct();

        $this->repo = $repo;
        $this->slack = Log::channel('slack-cl');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Craigslist Poster Instances
        $clients = $this->repo->getAllInternal();

        // Loop Posters
        $validation = [];
        foreach($clients as $client) {
            // Handle Validation
            $validation[] = $this->repo->validate($client);
        }

        // Check Client Status
        $status = $this->repo->status($validation);

        // Send Slack Message?
        if($status->isWarning()) {
            $this->slack->{$status->level}($status->message);
        }
    }
}