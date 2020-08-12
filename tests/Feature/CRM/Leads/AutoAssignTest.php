<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Support\Facades\Mail;
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


        // Refresh Leads
        $this->refreshLeads($dealer->id);

        // Build Random Factory Leads
        factory(Lead::class, 10)->create([
            'lead_type' => 'general'
        ]);
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);


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
            $salesPerson = $this->salespeople->roundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
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

            // Assert a lead status entry was saved...
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
     * Test round robin with empty preferred location
     * 
     * @specs int dealer_location_id = first in TEST_LOCATION_ID
     * @specs int last_location_id = last in TEST_LOCATION_ID
     * @specs string lead_type = inventory
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testNoPreferredLocationRoundRobin()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $dealer->crmUser()->update([
            'enable_assign_notification' => 1
        ]);

        // Build Random Factory Salespeople
        $locationIds = TestCase::getTestDealerLocationIds();
        $locationId = reset($locationIds);
        $lastLocationId = end($locationIds);


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

        // Force Default On Existing Items
        $salesQuery = SalesPerson::where('user_id', $dealer->crmUser->user_id)
                                 ->where('dealer_location_id', $lastLocationId);
        $salesQuery->update([
            'is_inventory' => 1
        ]);

        // Get Salespeople
        $salespeople = $salesQuery->get();
        if(empty($salespeople) || count($salespeople) < 3) {
            $add = (3 - count($salespeople));
            factory(SalesPerson::class, $add)->create([
                'dealer_location_id' => $lastLocationId
            ]);
        }


        // Get Inventory
        $inventory = Inventory::where('dealer_id', $dealer->id)
                              ->where('dealer_location_id', $lastLocationId)->first();
        if(empty($inventory) || empty($inventory->inventory_id)) {
            $inventory = factory(Inventory::class, 1)->create([
                'dealer_location_id' => $lastLocationId
            ]);
        }
        $inventoryId = $inventory->inventory_id;


        // Refresh Leads
        $this->refreshLeads($dealer->id);

        // Build Random Factory Leads With Location
        factory(Lead::class, 5)->create([
            'dealer_location_id' => $locationId,
            'inventory_id' => $inventoryId,
            'lead_type' => 'inventory'
        ]);

        // Build Random Factory Leads With No Location
        factory(Lead::class, 5)->create([
            'dealer_location_id' => 0,
            'inventory_id' => $inventoryId,
            'lead_type' => 'inventory'
        ]);

        // Build Random Factory Leads With No Location or Inventory
        factory(Lead::class, 5)->create([
            'dealer_location_id' => 0,
            'inventory_id' => 0,
            'lead_type' => 'inventory'
        ]);
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);


        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Newest Sales Person
            $salesType = 'inventory';

            // Get Dealer Location
            $dealerLocationId = $locationId;
            if(empty($lead->dealer_location_id)) {
                $dealerLocationId = $lastLocationId;
            }
            if(empty($lead->inventory_id)) {
                $dealerLocationId = 0;
            }

            // Find Newest Assigned Sales Person
            if(!isset($this->roundRobin[$dealer->id][$dealerLocationId][$salesType])) {
                $newestSalesPerson = $this->salespeople->findNewestSalesPerson($dealer->id, $dealerLocationId, $salesType);
            } else {
                $newestSalesPersonId = $this->roundRobin[$dealer->id][$dealerLocationId][$salesType];
                $newestSalesPerson = SalesPerson::find($newestSalesPersonId);
            }

            // Find Next!
            $salesPerson = $this->salespeople->roundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
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

            // Assert a lead status entry was saved...
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
     * Test round robin with some matches missing
     * 
     * @specs int dealer_location_id = first in TEST_LOCATION_ID
     * @specs string lead_type = inventory
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testNoMatchRoundRobin()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $dealer->crmUser()->update([
            'enable_assign_notification' => 1
        ]);

        // Build Random Factory Salespeople
        $locationIds = TestCase::getTestDealerLocationIds();
        $locationId = reset($locationIds);


        // Force Default On Existing Items
        $salesQuery = SalesPerson::where('user_id', $dealer->crmUser->user_id)
                                 ->where('dealer_location_id', $locationId);
        $salesQuery->update([
            'is_trade' => 0
        ]);

        // Get Salespeople
        $salespeople = $salesQuery->get();
        if(empty($salespeople) || count($salespeople) < 3) {
            $add = (3 - count($salespeople));
            factory(SalesPerson::class, $add)->create([
                'dealer_location_id' => $locationId,
                'is_trade' => 0
            ]);
        }


        // Refresh Leads
        $this->refreshLeads($dealer->id);

        // Build Random Factory Default Leads With Location
        factory(Lead::class, 5)->create([
            'dealer_location_id' => $locationId,
            'lead_type' => 'general'
        ]);

        // Build Random Factory Inventory Leads With Location
        factory(Lead::class, 5)->create([
            'dealer_location_id' => $locationId,
            'lead_type' => 'inventory'
        ]);

        // Build Random Factory Trade Leads With Location
        factory(Lead::class, 5)->create([
            'dealer_location_id' => $locationId,
            'lead_type' => 'trade'
        ]);
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);


        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Correct Sales Type
            $salesType = $lead->lead_type;
            if($salesType === 'general') {
                $salesType = 'default';
            }

            // Find Newest Assigned Sales Person
            if(!isset($this->roundRobin[$dealer->id][$locationId][$salesType])) {
                $newestSalesPerson = $this->salespeople->findNewestSalesPerson($dealer->id, $locationId, $salesType);
            } else {
                $newestSalesPersonId = $this->roundRobin[$dealer->id][$locationId][$salesType];
                $newestSalesPerson = SalesPerson::find($newestSalesPersonId);
            }

            // Find Next!
            $salesPerson = $this->salespeople->roundRobinSalesPerson($dealer->id, $locationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $salesPerson->id);
        }

        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . self::getTestDealerId())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {
            // Trade?!
            if($lead->lead_type === 'trade') {
                // Assert a lead assign entry was NOT saved...
                $this->assertDatabaseMissing('crm_tc_lead_status', [
                    'tc_lead_identifier' => $lead->identifier
                ]);
            } else {
                // Assert a message was sent to the given leads...
                $salesPerson = SalesPerson::find($leadSalesPeople[$lead->identifier]);
                Mail::assertSent(AutoAssignEmail::class, function ($mail) use ($salesPerson) {
                    if(empty($salesPerson->email)) {
                        return false;
                    }
                    return $mail->hasTo($salesPerson->email);
                });

                // Assert a lead status entry was saved...
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


        // Refresh Leads
        $this->refreshLeads($dealer->id);

        // Build Random Factory Leads
        factory(Lead::class, 5)->create([
            'dealer_location_id' => $locationId,
            'lead_type' => 'inventory'
        ]);
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);


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
            $salesPerson = $this->salespeople->roundRobinSalesPerson($dealer->id, $locationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
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

            // Assert a lead status entry was saved...
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
     * Refresh Unassigned Leads in DB
     * 
     * @param type $dealer->id
     * @return void
     */
    private function refreshLeads($dealer->id) {
        // Get Existing Unassigned Leads for Dealer ID
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);

        // Loop Leads
        foreach($leads as $lead) {
            Lead::where('identifier', $lead->identifier)->delete();
        }
    }

    /**
     * Preserve the Round Robin Sales Person Temporarily
     * 
     * @param int $dealer->id
     * @param int $dealerLocationId
     * @param string $salesType
     * @param int $salesPersonId
     * @return int last sales person ID
     */
    private function setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $salesPersonId) {
        // Assign to Arrays
        if(!isset($this->roundRobin[$dealer->id])) {
            $this->roundRobin[$dealer->id] = array();
        }

        // Match By Dealer Location ID!
        if(!empty($dealerLocationId)) {
            if(!isset($this->roundRobin[$dealer->id][$dealerLocationId])) {
                $this->roundRobin[$dealer->id][$dealerLocationId] = array();
            }
            $this->roundRobin[$dealer->id][$dealerLocationId][$salesType] = $salesPersonId;
        }

        // Always Set for 0!
        if(!isset($this->roundRobin[$dealer->id][0])) {
            $this->roundRobin[$dealer->id][0] = array();
        }
        $this->roundRobin[$dealer->id][0][$salesType] = $salesPersonId;

        // Return Last Sales Person ID
        return $this->roundRobin[$dealer->id][0][$salesType];
    }
}
