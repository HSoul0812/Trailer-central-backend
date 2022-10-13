<?php

namespace App\Console\Commands\CRM\Leads;

use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Services\CRM\Leads\HotPotatoServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HotPotato extends Command
{

    /**
     * The name and signature of the console command.
     * 
     * dealer determines that it should only run for the given dealer
     * boundLower determines the lowest dealer id to process
     * boundUpper determines the highest dealer id to process
     * @var string
     */
    protected $signature = 'leads:assign:hot-potato {boundLower?} {boundUpper?} {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassign leads to SalesPeople using Hot Potato method.';

    /**     
     * @var CrmUserRepositoryInterface
     */
    protected $crmUserRepository;
    
    /**     
     * @var HotPotatoServiceInterface
     */
    protected $hotPotatoService;
    
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
     * Create a new command instance.
     *
     * @param CrmUserRepositoryInterface $crmUserRepository
     * @param HotPotatoServiceInterface $hotPotatoService
     * @return void
     */
    public function __construct(
        CrmUserRepositoryInterface $crmUserRepository,
        HotPotatoServiceInterface $hotPotatoService
    ) {
        parent::__construct();

        $this->crmUserRepository = $crmUserRepository;
        $this->hotPotatoService = $hotPotatoService;

        date_default_timezone_set(config('app.db_timezone'));

        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(config('app.db_timezone')));
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        // Get Dealer ID
        $this->dealerId = $this->argument('dealer');
        
        $this->boundLower = $this->argument('boundLower');
        $this->boundUpper = $this->argument('boundUpper');        
        
        $now = $this->datetime->format("l, F jS, Y");
        $command = str_replace('{dealer?}', $this->dealerId, $this->signature);

        // Get Params for Dealer ID / Boundaries
        $params = [];
        if(!empty($this->dealerId)) {
            $params['dealer_id'] = $this->dealerId;
        } elseif(!empty($this->boundLower) || !empty($this->boundUpper)) {
            $params['min_dealer_id'] = $this->boundLower;
            $params['max_dealer_id'] = $this->boundUpper;
        }


        // Try Processing Hot Potato
        try {            
            $this->logOutput("{$command} started {$now}");

            $dealers = $this->crmUserRepository->getAll($params + [
                'enable_hot_potato' => 1
            ]);

            $this->logOutput("{$command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Valid Salespeople
            foreach($dealers as $dealer) {
                // Handle All Leads For Dealer
                $leads = $this->hotPotatoService->dealer($dealer->newDealerUser);
                $this->logOutput("{$command} found " . $leads->count() . " leads to process for dealer " . $dealer->id);
            }
        } catch(\Exception $e) {
            $this->logOutput("{$command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}", 'error');
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(config('app.db_timezone')));
        $this->logOutput("{$command} finished on " . $datetime->format("l, F jS, Y"));
    }


    /**
     * Log and Output Errors
     * 
     * @param string $msg
     * @param string $type
     * @return void
     */
    private function logOutput(string $msg, string $type = 'info'): void {
        // Initialize Logger for Hot Potato
        $log = Log::channel('hotpotato');

        // Log Error
        if($type === 'error') {
            $this->error($msg);
            $log->error($msg);
        } else {
            $this->info($msg);
            $log->info($msg);
        }
    }
}