<?php

namespace App\Console\Commands\CRM\Leads\Export;

use Illuminate\Console\Command;
use App\Repositories\CRM\Leads\Export\BigTexLeadRepositoryInterface;
use App\Services\CRM\Leads\Export\BigTexServiceInterface;
use Carbon\Carbon;

/**
 * Exports leads in BigTex format
 */
class BigTex extends Command
{    

    /**
     * The name and signature of the console command.
     * 
     * @var string
     */
    protected $signature = 'leads:export:bigtex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports leads to the BigTex system';

    /**     
     * @var App\Repositories\CRM\Leads\IDSLeadRepositoryInterface
     */
    protected $bigTexLeadRepository;
    
    /**     
     * @var App\Services\CRM\Leads\Export\IDSServiceInterface 
     */
    protected $bigTexService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BigTexLeadRepositoryInterface $leadRepo, BigTexServiceInterface $service)
    {
        parent::__construct();

        $this->bigTexLeadRepository = $leadRepo;
        $this->bigTexService = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $exportStartDate = Carbon::now()->subDays(40)->toDateTimeString();

        $this->info("Starting leads export...");

        $this->bigTexLeadRepository->getAllNotExportedChunked(function($leads) {
            foreach($leads as $lead) {
                $this->info("Processing lead {$lead->identifier}");
                $this->bigTexService->export($lead);
            }
        }, $exportStartDate);
    }
    
}
