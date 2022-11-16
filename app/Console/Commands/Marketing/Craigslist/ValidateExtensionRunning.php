<?php

namespace App\Console\Commands\Marketing\Craigslist;

use App\Repositories\Marketing\Craigslist\PosterRepositoryInterface;
use Illuminate\Console\Command;

/**
 * Class ValidateExtensionRunning
 * 
 * @package App\Console\Commands\Marketing\Craigslist
 */
class ValidateExtensionRunning extends Command
{
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

        // Get Marketplace Accounts
        $integrations = $this->repo->getAll(['dealer_id' => $dealerId]);
    }
}
