<?php

namespace App\Console\Commands\CRM\Leads\Import;

use App\Services\CRM\Leads\Import\ImportServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Import leads in the ADF format
 */
class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import leads.';

    /**
     * @var ImportServiceInterface
     */
    protected $service;

    public function __construct(ImportServiceInterface $service)
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
        $log = Log::channel('import');
        try {
            $imported = $this->service->import();

            // Return Result
            $this->info('Imported ' . $imported . ' leads from import service');
            $log->info('Imported ' . $imported . ' leads from import service');
        } catch (\Exception $e) {
            $this->error('Exception thrown parsing import: ' . $e->getMessage());
            $log->error('Exception thrown parsing import: ' . $e->getMessage() . '; ' . $e->getTraceAsString());
        }

        // Sleep for a Second to Prevent Rate Limiting
        sleep(1);
    }
}
