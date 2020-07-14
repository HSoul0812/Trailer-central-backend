<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Console\Command;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;

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
     * @var App\Repositories\CRM\User\SalesPersonRepository
     */
    protected $inventoryRepository;

    /**
     * @var array
     */
    private $lastSalesPeople = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LeadRepositoryInterface $leadRepo, SalesPersonRepositoryInterface $salesPersonRepo)
    {
        parent::__construct();

        $this->leadRepository = $leadRepo;
        $this->salesPersonRepository = $salesPersonRepo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Dealer ID
        $dealerId = $this->argument('dealer');
        $params = array(
            'per_page' => 100
        );
        if(!empty($dealerId)) {
            $params['dealer_id'] = $dealerId;
        }

        // Get Dealers With Sales People
        $dealers = $this->leadRepository->getUnassignedLeads($params);
        if(count($dealers) > 0) {
            foreach($dealers as $dealerId => $leads) {
                // Find All Sales People!
                $salesPeople = $this->salesPersonRepository->getAll([
                    'dealer_id' => $dealerId,
                    'per_page'  => 100
                ]);

                // Loop Leads
                foreach($leads as $lead) {
                    // Get Vars
                    $leadType = $this->salesPersonRepository->findSalesType($lead->lead_type);
                    $dealerLocationId = $lead->dealer_location_id;

                    // Find Next Salesperson
                    $salesPersonId = $this->salesPersonRepository->findNextSalesPerson($dealerId, $newestSalesPersonId, $dealerLocationId, $leadType);
                    if(empty($salesPersonId)) {
                        continue;
                    }

                    // Set Next Salesperson
                    $this->setLastSalesperson($dealerId, $dealerLocationId, $leadType, $salesPersonId);

                    // Set Salesperson to Lead
                    $this->leadRepository->saveSalesPerson($lead->identifier, $salesPersonId);
                }
            }
        }
    }
}
