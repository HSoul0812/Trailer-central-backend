<?php

namespace App\Console\Commands\CRM\Leads\Export;

use Illuminate\Console\Command;
use App\Repositories\CRM\Leads\Export\IDSLeadRepositoryInterface;
use App\Services\CRM\Leads\Export\IDSServiceInterface;

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
        $idsExportStartDate = config('ids.export_start_date');
        $this->idsLeadRepository->getAllNotExportedChunked(function($leads) {
            foreach($leads as $lead) {
                $this->idsService->export($lead);
            }
        }, $idsExportStartDate);
    }
    
}
