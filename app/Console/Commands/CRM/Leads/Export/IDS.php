<?php

namespace App\Console\Commands\CRM\Leads\Export;

use Illuminate\Console\Command;
use App\Repositories\CRM\Leads\Export\IDSLeadRepositoryInterface;
use App\Services\CRM\Leads\Export\IDSServiceInterface;
use Carbon\Carbon;

/**
 * Exports leads in IDS format
 */
class IDS extends Command
{    

    /**
     * The name and signature of the console command.
     * 
     * @var string
     */
    protected $signature = 'leads:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports leads to the IDS system';

    /**     
     * @var App\Repositories\CRM\Leads\IDSLeadRepositoryInterface
     */
    protected $idsLeadRepository;
    
    /**     
     * @var App\Services\CRM\Leads\Export\IDSServiceInterface 
     */
    protected $idsService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IDSLeadRepositoryInterface $leadRepo, IDSServiceInterface $idsService)
    {
        parent::__construct();

        $this->idsLeadRepository = $leadRepo;
        $this->idsService = $idsService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Log Start
        $idsExportStartDate = Carbon::now()->subDays(1)->toDateTimeString();
        $this->info("{$this->signature} starting leads export on {$idsExportStartDate}...");

        $this->idsLeadRepository->getAllNotExportedChunked(function($leads) {
            foreach($leads as $lead) {
                $this->info("{$this->signature} processing lead {$lead->identifier}");
                $this->idsService->export($lead);                
            }
        }, $idsExportStartDate);

        // Log End
        $idsExportEndDate = Carbon::now()->subDays(1)->toDateTimeString();
        $this->info("{$this->signature} finished on {$idsExportEndDate}");
    }
    
}
