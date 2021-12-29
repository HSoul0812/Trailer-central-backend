<?php

namespace App\Console\Commands\CRM\Interactions;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReimportInteractionMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interaction:messages:reimport {reimport?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-import interaction messages into elastic search.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Command
        $command = 'interaction:messages:reimport';

        // Try..Catch
        try {
            // Get ES Interaction Message
            $uri = env('ELASTICSEARCH_HOST') . '/interaction_message' . '?pretty';

            // Deleting Index
            $this->info("{$command} deleting elastic index: " . $uri);

            // Create Client
            $client = new Client();

            // Delete Request From Client
            $client->request('DELETE', $uri);
        } catch(\Exception $e) {
            $this->error('Error occurred clearing interaction_message index on ES: ' . $e->getMessage());
        }


        // Reimport?!
        $reimport = $this->argument('reimport');
        if(!empty($reimport)) {
            // Try..Catch
            try {
                // Get Call
                $call = 'scout:import "App\\\\Models\\\\CRM\\\\Interactions\\\\InteractionMessage" -v';

                // Deleting Index
                $this->info("{$command} recreating elastic index with artisan command: " . $call);

                // Re-Import ES Messages
                Artisan::call($call);
            } catch(\Exception $e) {
                $this->error('Error occurred re-imported index on ES: ' . $e->getMessage());
            }
        }
    }
}