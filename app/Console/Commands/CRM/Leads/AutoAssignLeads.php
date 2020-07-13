<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Console\Command;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\CrmUser;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;

class AutoAssignLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:autoassign {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Assign leads to SalesPeople.';

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
    public function __construct(SalesPersonRepositoryInterface $salesPersonRepo)
    {
        parent::__construct();

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

                // Find Newest Salesperson
                $newestSalesPersonId = $this->salesPersonRepository->findNewestSalesPerson($dealerId, $dealerLocationId, $leadType);

                // Loop Leads
                foreach($leads as $lead) {
                    // Find Next Salesperson
                    $salesPersonId = $this->findNextSalesperson($dealerId, $newestSalesPersonId, $dealerLocationId, $leadType);
                    if(empty($salesPersonId)) {
                        continue;
                    }

                    // Set Salesperson to Lead
                    $this->leadRepository->saveSalesPerson($lead->identifier, $salesPersonId);

                    // Set Next Salesperson
                    $this->setLastSalesperson($dealerId, $salesPersonId, $dealerLocationId, $leadType);
                }
            }
        }
    }

    /**
     * Set Last Sales Person
     * 
     * @param int $dealerId
     * @param int $salesPersonId
     * @param int $dealerLocationId
     * @param string $type
     * @return void
     */
    private function setLastSalesPerson($dealerId, $salesPersonId, $dealerLocationId, $type) {
        // Assign to Arrays
        if(!isset($this->lastSalesPeople[$dealerId])) {
            $this->lastSalesPeople[$dealerId] = array();
        }
        if(!isset($this->lastSalesPeople[$dealerId][$dealerLocationId])) {
            $this->lastSalesPeople[$dealerId][$dealerLocationId] = array();
        }
        $this->lastSalesPeople[$dealerId][$dealerLocationId][$type] = $salesPersonId;

        // Dealer Location ID Isn't 0?!
        if(!empty($dealerLocationId)) {
            // ALSO Set for 0!
            if(!isset($this->lastSalesPeople[$dealerId][0])) {
                $this->lastSalesPeople[$dealerId][0] = array();
            }
            $this->lastSalesPeople[$dealerId][0][$type] = $salesPersonId;
        }
    }
}
