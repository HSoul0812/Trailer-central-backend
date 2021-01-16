<?php

namespace App\Console\Commands\CRM\Leads\Import;

use Illuminate\Console\Command;
use App\Repositories\CRM\Leads\ImportRepositoryInterface;
use App\Services\CRM\Leads\Import\ADFImportServiceInterface;

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

    /**
     * @var App\Repositories\CRM\Leads\ImportRepositoryInterface
     */
    protected $imports;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImportRepositoryInterface $imports,
                                ADFImportServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->imports = $imports;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get All Imports
        $imports = $this->imports->getAllActive();
        $this->info("Importing ADF leads from " . count($imports) . " dealers and locations...");

        // Start Importing Leads
        $imported = $this->service->import();

        // Return Result
        $this->info("Imported " . $imported . " leads from ADF import service");
    }
}
