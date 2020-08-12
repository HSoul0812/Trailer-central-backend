<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Support\Facades\Mail;
use App\Repositories\CRM\Leads\LeadRepository;
use App\Repositories\CRM\User\SalesPersonRepository;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\Inventory\Inventory;
use App\Models\User\NewDealerUser;
use App\Mail\AutoAssignEmail;
use Tests\TestCase;

class AutoAssignTest extends TestCase
{
    /**
     * App\Repositories\CRM\Leads\LeadRepositoryInterface $leads
     * App\Repositories\CRM\Leads\SalesPersonRepositoryInterface $salespeople
     */
    protected $leads;
    protected $salespeople;

    /**
     * @var array $roundRobin
     */
    protected $roundRobin = [];

    /**
     * Set Up Test
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make Lead Repo
        $this->leads = $this->app->make('App\Repositories\CRM\Leads\LeadRepositoryInterface');
        $this->salespeople = $this->app->make('App\Repositories\CRM\User\SalesPersonRepositoryInterface');

        // Reset Round Robin Array
        $this->roundRobin = array();
    }

    /**
     * Test round robin only by location
     * 
     * @specs array dealer_location_id = all in TEST_LOCATION_ID
     * @specs string lead_type = general
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testLocationRoundRobin()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $dealer->crmUser()->update([
            'enable_assign_notification' => 1
        ]);

        // Build Random Factory Salespeople
        foreach(TestCase::getTestDealerLocationIds() as $locationId) {
            // Force Default On Existing Items
            $salesQuery = SalesPerson::where('user_id', $dealer->crmUser->user_id)
                                     ->where('dealer_location_id', $locationId);
            $salesQuery->update([
                'is_default' => 1
            ]);

            // Get Salespeople
            $salespeople = $salesQuery->get();
            if(empty($salespeople) || count($salespeople) < 3) {
                $add = (3 - count($salespeople));
                factory(SalesPerson::class, $add)->create([
                    'dealer_location_id' => $locationId
                ]);
            }
        }

        // Get Inventory
        $inventory = Inventory::where('dealer_id', $dealer->id)->take(5)->get();
        if(empty($inventory) || count($inventory) < 1) {
            $inventory = factory(Inventory::class, 5)->create();
        }

        // Get Leads
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);
        if(empty($leads) || count($leads) < 1) {
            // Build Random Factory Leads
            factory(Lead::class, 10)->create([
                'lead_type' => 'general'
            ]);
            $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);
        }

        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Newest Sales Person
            $salesType = 'default';
            $dealerLocationId = $lead->dealer_location_id;

            // Find Newest Assigned Sales Person
            if(!isset($this->roundRobin[$dealer->id][$dealerLocationId][$salesType])) {
                $newestSalesPerson = $this->salespeople->findNewestSalesPerson($dealer->id, $dealerLocationId, $salesType);
            } else {
                $newestSalesPersonId = $this->roundRobin[$dealer->id][$dealerLocationId][$salesType];
                $newestSalesPerson = SalesPerson::find($newestSalesPersonId);
            }

            // Find Next!
            $salesPerson = $this->salespeople->salespeople->roundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $salesPerson->id);
        }

        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . self::getTestDealerId())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {
            // Assert a message was sent to the given leads...
            $salesPerson = SalesPerson::find($leadSalesPeople[$lead->identifier]);
            Mail::assertSent(AutoAssignEmail::class, function ($mail) use ($salesPerson) {
                if(empty($salesPerson->email)) {
                    return false;
                }
                return $mail->hasTo($salesPerson->email);
            });

            // Assert a lead assign entry was saved...
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $lead->identifier,
                'sales_person_id' => $leadSalesPeople[$lead->identifier]
            ]);

            // Assert a lead assign entry was saved...
            $this->assertDatabaseHas('crm_lead_assign', [
                'dealer_id' => $dealer->id,
                'lead_id' => $lead->identifier,
                'chosen_salesperson_id' => $leadSalesPeople[$lead->identifier],
                'status' => 'mailed'
            ]);
        }
    }

    /**
     * Test round robin only with no email sent
     * 
     * @specs int dealer_location_id = first in TEST_LOCATION_ID
     * @specs string lead_type = inventory
     * @specs bool enable_assign_notification = 0
     * @return void
     */
    public function testNoEmailRoundRobin()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $dealer->crmUser()->update([
            'enable_assign_notification' => 0
        ]);

        // Build Random Factory Salespeople
        $locationIds = TestCase::getTestDealerLocationIds();
        $locationId = reset($locationIds);

        // Force Default On Existing Items
        $salesQuery = SalesPerson::where('user_id', $dealer->crmUser->user_id)
                                 ->where('dealer_location_id', $locationId);
        $salesQuery->update([
            'is_inventory' => 1
        ]);

        // Get Salespeople
        $salespeople = $salesQuery->get();
        if(empty($salespeople) || count($salespeople) < 3) {
            $add = (3 - count($salespeople));
            factory(SalesPerson::class, $add)->create([
                'dealer_location_id' => $locationId
            ]);
        }

        // Get Leads
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);
        if(empty($leads) || count($leads) < 1) {
            // Build Random Factory Leads
            factory(Lead::class, 5)->create([
                'dealer_location_id' => $locationId,
                'lead_type' => 'inventory'
            ]);
            $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);
        }

        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Newest Sales Person
            $salesType = 'inventory';

            // Find Newest Assigned Sales Person
            if(!isset($this->roundRobin[$dealer->id][$locationId][$salesType])) {
                $newestSalesPerson = $this->salespeople->findNewestSalesPerson($dealer->id, $locationId, $salesType);
            } else {
                $newestSalesPersonId = $this->roundRobin[$dealer->id][$locationId][$salesType];
                $newestSalesPerson = SalesPerson::find($newestSalesPersonId);
            }

            // Find Next!
            $salesPerson = $this->roundRobinSalesPerson($dealer->id, $locationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealer->id, $locationId, $salesType, $salesPerson->id);
        }

        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . self::getTestDealerId())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {
            // Assert a message was sent to the given leads...
            $salesPerson = SalesPerson::find($leadSalesPeople[$lead->identifier]);

            // Assert a lead assign entry was saved...
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $lead->identifier,
                'sales_person_id' => $leadSalesPeople[$lead->identifier]
            ]);

            // Assert a lead assign entry was saved...
            $this->assertDatabaseHas('crm_lead_assign', [
                'dealer_id' => $dealer->id,
                'lead_id' => $lead->identifier,
                'chosen_salesperson_id' => $leadSalesPeople[$lead->identifier],
                'status' => 'assigned'
            ]);
        }
    }


    /**
     * Preserve the Round Robin Sales Person Temporarily
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param int $salesPersonId
     * @return int last sales person ID
     */
    private function setRoundRobinSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPersonId) {
        // Assign to Arrays
        if(!isset($this->roundRobin[$dealerId])) {
            $this->roundRobin[$dealerId] = array();
        }

        // Match By Dealer Location ID!
        if(!empty($dealerLocationId)) {
            if(!isset($this->roundRobin[$dealerId][$dealerLocationId])) {
                $this->roundRobin[$dealerId][$dealerLocationId] = array();
            }
            $this->roundRobin[$dealerId][$dealerLocationId][$salesType] = $salesPersonId;
        }

        // Always Set for 0!
        if(!isset($this->roundRobin[$dealerId][0])) {
            $this->roundRobin[$dealerId][0] = array();
        }
        $this->roundRobin[$dealerId][0][$salesType] = $salesPersonId;

        // Return Last Sales Person ID
        return $this->roundRobin[$dealerId][0][$salesType];
    }
}
