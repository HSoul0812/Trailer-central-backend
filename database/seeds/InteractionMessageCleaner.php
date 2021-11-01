<?php

use Illuminate\Database\Seeder;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Artisan;

class InteractionMessageCleaner extends Seeder
{
    // Dealer ID
    const DEALER_ID = 1001;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Try..Catch
        try {
            // Get ES Interaction Message
            $uri = env('ELASTICSEARCH_HOST') . '/interaction_message' . '?pretty';

            // Create Client
            $client = new Client();

            // Delete Request From Client
            $client->request('DELETE', $uri);
        } catch(\Exception $e) {}


        // Re-Import ES Messages
        Artisan::call('scout:import "App\\Models\\CRM\\Interactions\\InteractionMessage" -v');
    }
}