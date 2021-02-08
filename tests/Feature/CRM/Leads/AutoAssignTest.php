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
        $websiteId = $dealer->website->id;

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
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
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
            $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $leadSalesPeople[$lead->identifier]);
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
        $websiteId = $dealer->website->id;

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


        // Get Inventory
        $inventory = Inventory::where('dealer_id', $dealer->id)
                              ->where('dealer_location_id', $lastLocationId)->first();
        if(empty($inventory) || empty($inventory->inventory_id)) {
            $inventory = factory(Inventory::class, 1)->create([
                'dealer_id' => $dealer->id,
                'dealer_location_id' => $lastLocationId
            ]);
        }
        $inventoryId = $inventory->inventory_id;


        // Refresh Leads
        $this->refreshLeads($dealer->id);

        // Build Random Factory Leads With Location
        factory(Lead::class, 5)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => $locationId,
            'inventory_id' => $inventoryId,
            'lead_type' => 'inventory'
        ]);

        // Build Random Factory Leads With No Location
        factory(Lead::class, 5)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => 0,
            'inventory_id' => $inventoryId,
            'lead_type' => 'inventory'
        ]);

        // Build Random Factory Leads With No Location or Inventory
        factory(Lead::class, 5)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
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
        $websiteId = $dealer->website->id;

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
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => $locationId,
            'lead_type' => 'general'
        ]);

        // Build Random Factory Inventory Leads With Location
        factory(Lead::class, 5)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => $locationId,
            'lead_type' => 'inventory'
        ]);

        // Build Random Factory Trade Leads With Location
        factory(Lead::class, 5)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
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
            $this->setRoundRobinSalesPerson($dealer->id, $locationId, $salesType, $leadSalesPeople[$lead->identifier]);
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
     * Test no round robin; all entries must match exactly one thing
     * 
     * @specs array dealer_location_id = all in TEST_LOCATION_ID
     * @specs array lead_type = general, inventory, trade
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testNoRoundRobin()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $dealer->crmUser()->update([
            'enable_assign_notification' => 1
        ]);
        $websiteId = $dealer->website->id;

        // Clean Up Salespeople
        $this->roundRobin[$dealer->id] = array();
        foreach(TestCase::getTestDealerLocationIds() as $locationId) {
            // Get Sales People for Location
            $salespeople = SalesPerson::where('user_id', $dealer->crmUser->user_id)
                                      ->where('dealer_location_id', $locationId)->get();

            // Loop Salespeople
            $this->roundRobin[$dealer->id][$locationId] = array();
            $roundRobinLocation = array();
            foreach($salespeople as $salesPerson) {
                $roundRobinLocation[] = $salesPerson;
            }

            // Loop Valid Types
            foreach(SalesPerson::TYPES_VALID as $salesType) {
                // Initialize Sales Type
                $this->roundRobin[$dealer->id][$locationId][$salesType] = array();

                // Get First Sales Person
                $salesPerson = array_shift($roundRobinLocation);
                if(!empty($salesPerson->id)) {
                    // Toggle Sales Person to ONLY Work With This Sales Type
                    $params = [
                        'is_default' => 0,
                        'is_inventory' => 0,
                        'is_financing' => 0,
                        'is_trade' => 0
                    ];
                    $params['is_' . $salesType] = 1;
                    SalesPerson::where('id', $salesPerson->id)->update($params);
                } else {
                    // Create Sales Person!
                    $params = [
                        'user_id' => $dealer->crmUser->user_id,
                        'dealer_location_id' => $locationId,
                        'is_default' => 0,
                        'is_inventory' => 0,
                        'is_financing' => 0,
                        'is_trade' => 0
                    ];
                    $params['is_' . $salesType] = 1;
                    $salespeople = factory(SalesPerson::class, 1)->create($params);
                    $salesPerson = reset($salespeople);
                    var_dump($salespeople);
                    var_dump($salesPerson);
                    die;
                }

                // Set Sales Person ID
                $this->roundRobin[$dealer->id][$locationId][$salesType] = $salesPerson->id;
            }

            // Delete Remaining Locations!
            if(count($roundRobinLocation) > 0) {
                foreach($roundRobinLocation as $salesPerson) {
                    SalesPerson::where('id', $salesPerson->id)->delete();
                }
            }
        }


        // Refresh Leads
        $this->refreshLeads($dealer->id);

        // Build Random Factory Default Leads For Each Location
        foreach(TestCase::getTestDealerLocationIds() as $locationId) {
            factory(Lead::class, 2)->create([
                'website_id' => $websiteId,
                'dealer_id' => $dealer->id,
                'dealer_location_id' => $locationId,
                'lead_type' => 'general'
            ]);
            factory(Lead::class, 2)->create([
                'website_id' => $websiteId,
                'dealer_id' => $dealer->id,
                'dealer_location_id' => $locationId,
                'lead_type' => 'inventory'
            ]);
            factory(Lead::class, 2)->create([
                'website_id' => $websiteId,
                'dealer_id' => $dealer->id,
                'dealer_location_id' => $locationId,
                'lead_type' => 'trade'
            ]);
            factory(Lead::class, 2)->create([
                'website_id' => $websiteId,
                'dealer_id' => $dealer->id,
                'dealer_location_id' => $locationId,
                'lead_type' => 'financing'
            ]);
        }
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);


        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Correct Sales Type
            $salesType = $lead->lead_type;
            if($salesType === 'general') {
                $salesType = 'default';
            }

            // We Should Know EXACTLY Where it Goes!
            $leadSalesPeople[$lead->identifier] = $this->roundRobin[$dealer->id][$lead->dealer_location_id][$salesType];
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
     * Test round robin on consecutive runs
     * 
     * @specs int dealer_location_id = first in TEST_LOCATION_ID
     * @specs string lead_type = inventory
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testConsecutiveRoundRobin()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $dealer->crmUser()->update([
            'enable_assign_notification' => 1
        ]);
        $websiteId = $dealer->website->id;

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
        $salesType = 'inventory';
        $salespeople = $salesQuery->get();
        if(empty($salespeople) || count($salespeople) < 3) {
            $add = (3 - count($salespeople));
            factory(SalesPerson::class, $add)->create([
                'dealer_location_id' => $locationId,
                'lead_type' => $salesType
            ]);
        }

        // Fake Mail
        Mail::fake();


        // Refresh Leads
        $this->refreshLeads($dealer->id);

        // Build Random Factory Leads
        factory(Lead::class, 1)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => $locationId,
            'lead_type' => $salesType
        ]);
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);

        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Find Newest Assigned Sales Person
            $newestSalesPerson = $this->salespeople->findNewestSalesPerson($dealer->id, $locationId, $salesType);

            // Find Next!
            $salesPerson = $this->salespeople->roundRobinSalesPerson($dealer->id, $locationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealer->id, $locationId, $salesType, $salesPerson->id);
        }

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . self::getTestDealerId())->assertExitCode(0);


        // Build Random Factory Leads
        factory(Lead::class, 5)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => $locationId,
            'lead_type' => $salesType
        ]);
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);

        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Find Newest Assigned Sales Person
            $newestSalesPersonId = $this->roundRobin[$dealer->id][$locationId][$salesType];
            $newestSalesPerson = SalesPerson::find($newestSalesPersonId);

            // Find Next!
            $salesPerson = $this->salespeople->roundRobinSalesPerson($dealer->id, $locationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealer->id, $locationId, $salesType, $salesPerson->id);
        }

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . self::getTestDealerId())->assertExitCode(0);


        // Build Random Factory Leads
        factory(Lead::class, 3)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => $locationId,
            'lead_type' => $salesType
        ]);
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealer->id]);

        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Find Newest Assigned Sales Person
            $newestSalesPersonId = $this->roundRobin[$dealer->id][$locationId][$salesType];
            $newestSalesPerson = SalesPerson::find($newestSalesPersonId);

            // Find Next!
            $salesPerson = $this->salespeople->roundRobinSalesPerson($dealer->id, $locationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealer->id, $locationId, $salesType, $salesPerson->id);
        }

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
        $websiteId = $dealer->website->id;

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
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
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
    private function refreshLeads($dealerId) {
        // Get Existing Unassigned Leads for Dealer ID
        $leads = $this->leads->getAllUnassigned(['dealer_id' => $dealerId]);

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
