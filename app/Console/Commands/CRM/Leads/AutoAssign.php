<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Console\Command;
use App\Models\User\NewDealerUser;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class AutoAssign extends Command
{    

    /**
     * The name and signature of the console command.
     * 
     * dealer determines that it should only run for the given dealer
     * boundLower determines the lowest dealer id to process
     * boundUpper determines the highest dealer id to process
     * @var string
     */
    protected $signature = 'leads:assign:auto {boundLower?} {boundUpper?} {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Assign leads to SalesPeople.';

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
     * @var int
     */
    protected $boundLower;
    
    /**    
     * @var int
     */
    protected $boundUpper;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->autoAssignService = resolve(AutoAssignServiceInterface::class);

        date_default_timezone_set(config('app.db_timezone'));

        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(config('app.db_timezone')));

        // Get Dealer ID
        $this->dealerId = $dealerId = $this->argument('dealer');
        
        $this->boundLower = $this->argument('boundLower');
        $this->boundUpper = $this->argument('boundUpper');        
        
        $now = $this->datetime->format("l, F jS, Y");
        $command = str_replace('{dealer?}', $dealerId, $this->signature);
        
        try {            
            $this->info("{$command} started {$now}");
            
            $dealers = $this->getDealersToProcess();
            
            $this->info("{$command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Valid Salespeople
            foreach($dealers as $dealer) {
                // Handle All Leads For Dealer
                $leads = $this->autoAssignService->dealer($dealer);
                $this->info("{$command} found " . $leads->count() . " leads to process for dealer " . $dealer->id);
            }
        } catch(\Exception $e) {
            $this->error("{$command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
            Log::channel('autoassign')->error('Exception returned processing auto assign command: ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(config('app.db_timezone')));
        $this->info("{$command} finished on " . $datetime->format("l, F jS, Y"));
    }


    /**
     * Get Dealers to Process
     * 
     * @return Collection<NewDealerUser>
     */
    private function getDealersToProcess(): Collection {
        $dealers = new Collection();
        if(!empty($this->dealerId)) {
            // Get Single Dealer
            $dealer = NewDealerUser::findOrFail($this->dealerId);
            $dealers->push($dealer);
        } else if ($this->boundLower && $this->boundUpper) {
            // Get Dealers In Range
            $dealers = NewDealerUser::where('id', '>=', $this->boundLower)
                            ->where('id', '<=', $this->boundUpper)
                            ->has('activeCrmUser')
                            ->has('salespeopleEmails')
                            ->get();
        } else if ($this->boundLower) {
            // Get Dealers From Minimum
            $dealers = NewDealerUser::where('id', '>=', $this->boundLower)
                            ->has('activeCrmUser')
                            ->has('salespeopleEmails')
                            ->get();
        } else {
            // Get All Dealers
            $dealers = NewDealerUser::has('activeCrmUser')->has('salespeopleEmails')->get();
        }

        // Return Dealers Collection
        return $dealers;
    }
}
