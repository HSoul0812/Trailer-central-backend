<?php

namespace App\Console\Commands\CRM\Leads\Export;

use App\Repositories\CRM\Leads\Export\ADFLeadRepositoryInterface;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * Exports leads in ADF format
 */
class ADF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:export:adf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports leads in ADF format';

    /**
     * @var ADFLeadRepositoryInterface
     */
    private $adfLeadRepository;

    /**
     * @var ADFServiceInterface
     */
    private $adfService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ADFLeadRepositoryInterface $adfLeadRepo, ADFServiceInterface $adfService)
    {
        parent::__construct();

        $this->adfLeadRepository = $adfLeadRepo;
        $this->adfService = $adfService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $adfExportStartDate = Carbon::now()->subDays(1)->toDateTimeString();

        $this->adfLeadRepository->getAllNotExportedChunked(function ($leads) {
            $this->info("Start processing " . count($leads) . " leads to export...\n====================");

            foreach ($leads as $lead) {
                $this->info("Processing lead {$lead->identifier}");

                try {
                    if (!$this->adfService->export($lead)) {
                        continue;
                    };
                    $this->info("Lead {$lead->identifier} added to queue");
                } catch (\Exception $e) {
                    $this->error("Error processing lead {$lead->identifier}: " . $e->getMessage());
                    continue;
                }
                $this->info("====================");
            }
        }, $adfExportStartDate);

        $this->info("Lead processing completed.");
    }
}
