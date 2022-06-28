<?php

namespace App\Console\Commands\CRM\Leads\Import;

use Illuminate\Console\Command;

/**
 * Import leads in the ADF format
 */
class ADF extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:import:adf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import leads using ADF.';

    /**
     * @var App\Services\CRM\Leads\Import\ADFImportServiceInterface
     */
    protected $service;

    public function __construct(ADFServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        // Start Importing Leads
        $imported = $this->service->import();

        // Return Result
        $this->info("Imported " . $imported . " leads from ADF import service");

        // Sleep for a Second to Prevent Rate Limiting
        sleep(1);
    }
}
