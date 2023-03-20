<?php

namespace App\Console\Commands\Marketing\Craigslist;

use App\Repositories\Marketing\Craigslist\ClientRepositoryInterface;
use App\Services\Marketing\Craigslist\ValidateServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        // Log Client
        $log = Log::channel('cl-client');

        // CL Warning Enabled
        $isEnabled = config('marketing.cl.settings.warning.enabled', '0');
        if(!(int) $isEnabled) {
            return false;
        }

        // Get Craigslist Poster Instances
        $clients = $this->repo->getAllInternal();
        $log->info('Validating ' . $clients->count() . ' CL Clients are Actively Running!');

        // Loop Posters
        $validation = new Collection();
        foreach($clients as $client) {
            // Handle Validation
            if($client instanceof Behaviour) {
                $validation->push($this->service->expired($client));
            } else {
                $validation->push($this->service->validate($client));
            }
        }

        // Check Client Status
        $log->info('Return Status of ' . $validation->count() . ' CL Clients ' .
                    'Per Allowed Internal Email Address');
        $messages = $this->service->status($validation);

        // Send Slack Messages?
        if($messages->count() > 0) {
            $log->info('Sending ' . $messages->count() . ' Slack Messages for ' .
                        $clients->count() . ' CL Clients');
            foreach($messages as $message) {
                $this->service->send($message);
            }
        } else {
            $log->info('No Slack Messages To Send for CL Clients');
        }

        // Count Scheduled Posts and Send Back to Server
        $this->service->counts();
    }
}