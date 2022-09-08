<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Support\Facades\Mail;
use App\Models\CRM\User\SalesPerson;
use App\Mail\AutoAssignEmail;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use Tests\database\seeds\CRM\Leads\LeadSeeder;
use Tests\TestCase;

class AutoAssignTest extends TestCase
{
    /**
     * @var LeadSeeder
     */
    private $seeder;

    /**
     * @var array $roundRobin
     */
    protected $roundRobin = [];


    /**
     * Test round robin only by location
     * 
     * @group CRM
     * @specs array dealer_location_id = exists
     * @specs string lead_type = general
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testLocationRoundRobin()
    {
        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $dealerId = $this->seeder->dealer->getKey();


        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            $salesType = 'trade';
            if(empty($lead->dealer_location_id) && $lead->lead_type !== $salesType) {
                continue;
            }

            // Get Newest Sales Person
            $dealerLocationId = $lead->dealer_location_id;

            // Find Newest Assigned Sales Person
            if(!isset($this->roundRobin[$dealerId][$dealerLocationId][$salesType])) {
                $newestSalesPerson = $this->getSalesPersonRepository()->findNewestSalesPerson($dealerId, $dealerLocationId, $salesType);
            } else {
                $newestSalesPersonId = $this->roundRobin[$dealerId][$dealerLocationId][$salesType];
                $newestSalesPerson = SalesPerson::find($newestSalesPersonId);
            }

            // Find Next!
            $salesPerson = $this->getSalesPersonRepository()->roundRobinSalesPerson($this->seeder->newDealerUser, $dealerLocationId, $salesType, $newestSalesPerson);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealerId, $dealerLocationId, $salesType, $leadSalesPeople[$lead->identifier]);
        }

        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . $dealerId)->assertExitCode(0);


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
                'dealer_id' => $dealerId,
                'lead_id' => $lead->identifier,
                'chosen_salesperson_id' => $leadSalesPeople[$lead->identifier],
                'status' => 'mailed'
            ]);
        }
    }

    /**
     * Test round robin with empty preferred location
     * 
     * @group CRM
     * @specs int dealer_location_id = first in TEST_LOCATION_ID
     * @specs int last_location_id = last in TEST_LOCATION_ID
     * @specs string lead_type = inventory
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testNoPreferredLocationRoundRobin()
    {
        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $dealerId = $this->seeder->dealer->getKey();


        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            $salesType = 'inventory';
            if($lead->lead_type !== $salesType) {
                continue;
            }

            // Get Dealer Location
            $dealerLocationId = $lead->dealer_location_id;
            if(empty($dealerLocationId)) {
                $dealerLocationId = 0;
            }

            // Find Newest Assigned Sales Person
            if(!isset($this->roundRobin[$dealerId][$dealerLocationId][$salesType])) {
                $newestSalesPerson = $this->getSalesPersonRepository()->findNewestSalesPerson($dealerId, $dealerLocationId, $salesType);
            } else {
                $newestSalesPersonId = $this->roundRobin[$dealerId][$dealerLocationId][$salesType];
                $newestSalesPerson = SalesPerson::find($newestSalesPersonId);
            }

            // Find Next!
            $salesPerson = $this->getSalesPersonRepository()->roundRobinSalesPerson($this->seeder->newDealerUser, $dealerLocationId, $salesType, $newestSalesPerson);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPerson->id);
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
                'dealer_id' => $dealerId,
                'lead_id' => $lead->identifier,
                'chosen_salesperson_id' => $leadSalesPeople[$lead->identifier],
                'status' => 'mailed'
            ]);
        }
    }

    /**
     * Test round robin with some matches missing
     * 
     * @group CRM
     * @specs int dealer_location_id = first in TEST_LOCATION_ID
     * @specs string lead_type = inventory
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testNoMatchRoundRobin()
    {
        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $dealerId = $this->seeder->dealer->getKey();


        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Correct Sales Type
            $salesType = $lead->lead_type;
            if($salesType === 'general') {
                $salesType = 'default';
            }

            // Find Newest Assigned Sales Person
            if(!isset($this->roundRobin[$dealerId][$locationId][$salesType])) {
                $newestSalesPerson = $this->getSalesPersonRepository()->findNewestSalesPerson($dealerId, $locationId, $salesType);
            } else {
                $newestSalesPersonId = $this->roundRobin[$dealerId][$locationId][$salesType];
                $newestSalesPerson = SalesPerson::find($newestSalesPersonId);
            }

            // Find Next!
            $salesPerson = $this->getSalesPersonRepository()->roundRobinSalesPerson($this->seeder->newDealerUser, $locationId, $salesType, $newestSalesPerson);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealerId, $locationId, $salesType, $leadSalesPeople[$lead->identifier]);
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
                    'dealer_id' => $dealerId,
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
     * @group CRM
     * @specs array dealer_location_id = all in TEST_LOCATION_ID
     * @specs array lead_type = general, inventory, trade
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testNoRoundRobin()
    {
        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $dealerId = $this->seeder->dealer->getKey();


        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Correct Sales Type
            $salesType = $lead->lead_type;
            if($salesType === 'general') {
                $salesType = 'default';
            }

            // We Should Know EXACTLY Where it Goes!
            $leadSalesPeople[$lead->identifier] = $this->roundRobin[$dealerId][$lead->dealer_location_id][$salesType];
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
                'dealer_id' => $dealerId,
                'lead_id' => $lead->identifier,
                'chosen_salesperson_id' => $leadSalesPeople[$lead->identifier],
                'status' => 'mailed'
            ]);
        }
    }

    /**
     * Test round robin only with no email sent
     * 
     * @group CRM
     * @specs int dealer_location_id = first in TEST_LOCATION_ID
     * @specs string lead_type = inventory
     * @specs bool enable_assign_notification = 0
     * @return void
     */
    public function testNoEmailRoundRobin()
    {
        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $dealerId = $this->seeder->dealer->getKey();


        // Detect What Sales People Will be Assigned!
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Newest Sales Person
            $salesType = 'inventory';

            // Find Newest Assigned Sales Person
            if(!isset($this->roundRobin[$dealerId][$locationId][$salesType])) {
                $newestSalesPerson = $this->getSalesPersonRepository()->findNewestSalesPerson($dealerId, $locationId, $salesType);
            } else {
                $newestSalesPersonId = $this->roundRobin[$dealerId][$locationId][$salesType];
                $newestSalesPerson = SalesPerson::find($newestSalesPersonId);
            }

            // Find Next!
            $salesPerson = $this->getSalesPersonRepository()->roundRobinSalesPerson($this->seeder->newDealerUser, $locationId, $salesType, $newestSalesPerson);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $this->setRoundRobinSalesPerson($dealerId, $locationId, $salesType, $salesPerson->id);
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
                'dealer_id' => $dealerId,
                'lead_id' => $lead->identifier,
                'chosen_salesperson_id' => $leadSalesPeople[$lead->identifier],
                'status' => 'assigned'
            ]);
        }
    }


    /**
     * Preserve the Round Robin Sales Person Temporarily
     * 
     * @group CRM
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



    /**
     * Set Up Seeder
     * 
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make Lead Seeder
        $this->seeder = new LeadSeeder();
        $this->seeder->seed();
    }

    /**
     * Tear Down Seeder
     * 
     * @return void
     */
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }


    /**
     * @return LeadRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getLeadRepository(): LeadRepositoryInterface
    {
        return $this->app->make(LeadRepositoryInterface::class);
    }

    /**
     * @return SalesPersonRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getSalesPersonRepository(): SalesPersonRepositoryInterface
    {
        return $this->app->make(SalesPersonRepositoryInterface::class);
    }
}
