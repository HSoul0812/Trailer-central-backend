<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Console\Command;
use App\Models\User\NewDealerUser;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Services\CRM\Leads\AutoAssignServiceInterface;

class AutoAssign extends Command
{    

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:assign:auto {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Assign leads to SalesPeople.';

    /**     
     * @var App\Repositories\CRM\Leads\LeadRepository
     */
    protected $leadRepository;

    /**     
     * @var App\Repositories\CRM\User\SalesPersonRepositoryInterface
     */
    protected $salesPersonRepository;
    
    /**     
     * @var App\Services\CRM\Leads\AutoAssignServiceInterface
     */
    protected $autoAssignService;
    
    /**
     * @var datetime
     */
    protected $datetime = null;
       
    /**    
     * @var int
     */
    protected $dealerId;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LeadRepositoryInterface $leadRepo, AutoAssignServiceInterface $autoAssignService)
    {
        parent::__construct();

        $this->leadRepository = $leadRepo;
        $this->autoAssignService = $autoAssignService;
        
        date_default_timezone_set(env('DB_TIMEZONE'));
        
        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Dealer ID
        $this->dealerId = $dealerId = $this->argument('dealer');
        
        $now = $this->datetime->format("l, F jS, Y");
        $command = str_replace('{dealer?}', $dealerId, $this->signature);
        
        try {            
            $this->info("{$command} started {$now}");
            
            $dealers = $this->getDealersToProcess();
            
            $this->info("{$command} found " . count($dealers) . " dealers to process");
            
            // Get Dealers With Valid Salespeople
            foreach($dealers as $dealer) {
                // Get Unassigned Leads
                $leads = $this->leadRepository->getAllUnassigned([
                    'per_page' => 'all',
                    'dealer_id' => $dealer->id
                ]);
                                
                if(count($leads) < 1) {
                    $this->info("{$command} skipping dealer {$dealer->id} because there are no pending leads");
                    continue;
                }

                $this->info("{$command} dealer #{$dealer->id} found " . count($leads) . " to process");

                foreach($leads as $lead) {                    
                    $this->autoAssignService->autoAssign($lead);
                }
            }
        } catch(\Exception $e) {
            $this->error("{$command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $this->info("{$command} finished on " . $datetime->format("l, F jS, Y"));
    }

    
    private function getDealersToProcess() {
        $dealers = array();
        if(!empty($this->dealerId)) {
            $dealer = NewDealerUser::findOrFail($this->dealerId);
            $dealers[] = $dealer;
        } else {
            $dealers = NewDealerUser::has('activeCrmUser')->has('salespeopleEmails')->get();
        }
        return $dealers;
    }
}
