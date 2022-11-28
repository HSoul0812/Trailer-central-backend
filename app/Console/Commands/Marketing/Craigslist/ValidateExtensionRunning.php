<?php

namespace App\Console\Commands\Marketing\Craigslist;

use App\Repositories\Marketing\Craigslist\ClientRepositoryInterface;
use App\Services\Marketing\Craigslist\ValidateServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Class ValidateExtensionRunning
 * 
 * @package App\Console\Commands\Marketing\Craigslist
 */
class ValidateExtensionRunning extends Command
{
    /**
     * @var ClientRepositoryInterface
     */
    private $repo;

    /**
     * @var ValidateServiceInterface
     */
    private $service;


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
     * @param ClientRepositoryInterface $repo
     * @param ValidateServiceInterface $service
     * @return void
     */
    public function __construct(ClientRepositoryInterface $repo, ValidateServiceInterface $service)
    {
        parent::__construct();

        $this->repo = $repo;
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get Craigslist Poster Instances
        $clients = $this->repo->getAllInternal();

        // Loop Posters
        $validation = new Collection();
        foreach($clients as $client) {
            // Handle Validation
            $validation->push($this->service->validate($client));
        }

        // Check Client Status
        $messages = $this->service->status($validation);

        // Send Slack Messages?
        if($messages->count() > 0) {
            foreach($messages as $message) {
                $this->service->send($message);
            }
        }
    }
}