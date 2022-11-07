<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Support\Facades\Mail;
use App\Mail\AutoAssignEmail;
use App\Mail\CRM\Leads\HotPotatoEmail;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use Tests\database\seeds\CRM\Leads\HotPotatoSeeder;
use Tests\TestCase;

class HotPotatoTest extends TestCase
{
    /**
     * @var LeadSeeder
     */
    private $seeder;


    /**
     * Test hot potato only by location
     * 
     * @group CRM
     * @specs array dealer_location_id = exists
     * @specs string lead_type = inventory/trade
     * @specs bool enable_assign_notification = 0
     * @return void
     */
    public function testHotPotato()
    {
        // Seed Database With Auto Assign Leads
        $this->seeder->seed();

        // Given I have a collection of leads and sales people
        $leads = $this->seeder->leads;
        $sales = $this->seeder->sales;


        // Based on the seeder results, we should know what sales person is assigned to who:
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[1];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[1];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[0];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[2];


        // Fake Mail
        Mail::fake();

        sleep(10);

        // Get dealer and location
        $dealer = $this->seeder->dealer;
        $location = $this->seeder->location;
        $dealerId = $this->seeder->dealer->getKey();

        // Call Leads Assign Command
        $this->artisan('leads:assign:hot-potato ' . $dealerId)->assertExitCode(0);


        // Loop Leads
        foreach($leadSalesPeople as $i => $salesPerson) {
            // Initialize Lead
            $lead = $leads[$i];
            $leadId = $lead->identifier;

            // Assert a message was sent to the given leads...
            Mail::assertSent(HotPotatoEmail::class, function ($mail) use ($lead, $dealer, $location) {
                if(!empty($lead->dealer_location_id)) {
                    return $mail->hasTo($location->email);
                } else {
                    return $mail->hasTo($dealer->email);
                }
            });

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $leadId,
                'sales_person_id' => $salesPerson->getKey()
            ]);

            // Assert a lead assign entry was saved...
            $this->assertDatabaseHas('crm_lead_assign', [
                'dealer_id' => $dealerId,
                'lead_id' => $leadId,
                'chosen_salesperson_id' => $salesPerson->getKey(),
                'status' => 'mailed'
            ]);
        }
    }

    /**
     * Test hot potato with some no matches
     * 
     * @group CRM
     * @specs array dealer_location_id = exists
     * @specs string lead_type = inventory/trade
     * @specs bool enable_assign_notification = 0
     * @return void
     */
    public function testNoMatches()
    {
        // Seed Database With Auto Assign Leads
        $this->seeder->seedNoMatches();

        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $sales = $this->seeder->sales;


        // Based on the seeder results, we should know what sales person is assigned to who:
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[1];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $this->seeder->salesPerson;
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[0];
        $leadSalesPeople[] = $this->seeder->salesPerson;
        $leadSalesPeople[] = $sales[2];


        // Fake Mail
        Mail::fake();
        
        sleep(10);

        // Get dealer and location
        $dealer = $this->seeder->dealer;
        $location = $this->seeder->location;
        $dealerId = $this->seeder->dealer->getKey();

        // Call Leads Assign Command
        $this->artisan('leads:assign:hot-potato ' . $dealerId)->assertExitCode(0);


        // Loop Leads
        foreach($leadSalesPeople as $i => $salesPerson) {
            // Initialize Lead
            $lead = $leads[$i];
            $leadId = $lead->identifier;

            // Assert a message was sent to the given leads...
            Mail::assertSent(HotPotatoEmail::class, function ($mail) use ($lead, $dealer, $location) {
                if(!empty($lead->dealer_location_id)) {
                    return $mail->hasTo($location->email);
                } else {
                    return $mail->hasTo($dealer->email);
                }
            });

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $leadId,
                'sales_person_id' => !empty($salesPerson) ? $salesPerson->getKey() : null
            ]);

            // Assert a lead assign entry was saved...
            if(!empty($salesPerson)) {
                $this->assertDatabaseHas('crm_lead_assign', [
                    'dealer_id' => $dealerId,
                    'lead_id' => $leadId,
                    'chosen_salesperson_id' => $salesPerson->getKey(),
                    'status' => 'mailed'
                ]);
            } else {
                $this->assertDatabaseHas('crm_lead_assign', [
                    'dealer_id' => $dealerId,
                    'lead_id' => $leadId,
                    'chosen_salesperson_id' => 0,
                    'status' => 'skipped'
                ]);
            }
        }
    }

    /**
     * Test hot potato without sending assign email
     * 
     * @group CRM
     * @specs array dealer_location_id = exists
     * @specs string lead_type = general
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testAssignEmail()
    {
        // Seed Database With Auto Assign Leads
        $this->seeder->seed();
        $this->seeder->enableAssignEmail(1);

        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $sales = $this->seeder->sales;


        // Based on the seeder results, we should know what sales person is assigned to who:
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[1];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[1];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[0];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[2];


        // Fake Mail
        Mail::fake();
        
        sleep(10);

        // Get dealer and location
        $dealer = $this->seeder->dealer;
        $location = $this->seeder->location;
        $dealerId = $this->seeder->dealer->getKey();

        // Call Leads Assign Command
        $this->artisan('leads:assign:hot-potato ' . $dealerId)->assertExitCode(0);


        // Loop Leads
        foreach($leadSalesPeople as $i => $salesPerson) {
            // Initialize Lead
            $lead = $leads[$i];
            $leadId = $lead->identifier;

            // Assert a message was sent to the given leads...
            Mail::assertSent(HotPotatoEmail::class, function ($mail) use ($lead, $dealer, $location) {
                if(!empty($lead->dealer_location_id)) {
                    return $mail->hasTo($location->email);
                } else {
                    return $mail->hasTo($dealer->email);
                }
            });

            // Assert a message was sent to the given leads...
            if(!empty($salesPerson)) {
                Mail::assertSent(AutoAssignEmail::class, function ($mail) use ($salesPerson) {
                    if(empty($salesPerson->email)) {
                        return false;
                    }                
                    return $mail->hasTo($salesPerson->email);
                });
            }

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $leadId,
                'sales_person_id' => $salesPerson->getKey()
            ]);

            // Assert a lead assign entry was saved...
            $this->assertDatabaseHas('crm_lead_assign', [
                'dealer_id' => $dealerId,
                'lead_id' => $leadId,
                'chosen_salesperson_id' => $salesPerson->getKey(),
                'status' => 'mailed'
            ]);
        }
    }

    /**
     * Test hot potato with fallback to units of interest
     * 
     * @group CRM
     * @specs array dealer_location_id = exists
     * @return void
     */
    public function testWithUnits()
    {
        // Seed Database With Auto Assign Leads
        $this->seeder->seedWithUnits();

        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $sales = $this->seeder->sales;


        // Based on the seeder results, we should know what sales person is assigned to who:
        $leadSalesPeople[] = $sales[3];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[0];
        $leadSalesPeople[] = $sales[2];
        $leadSalesPeople[] = $sales[0];
        $leadSalesPeople[] = $sales[1];
        $leadSalesPeople[] = $sales[0];
        $leadSalesPeople[] = $sales[3];


        // Fake Mail
        Mail::fake();
        
        sleep(10);

        // Get dealer and location
        $dealer = $this->seeder->dealer;
        $location = $this->seeder->location;
        $location2 = $this->seeder->location2;
        $dealerId = $this->seeder->dealer->getKey();

        // Call Leads Assign Command
        $this->artisan('leads:assign:hot-potato ' . $dealerId)->assertExitCode(0);


        // Loop Leads
        foreach($leadSalesPeople as $i => $salesPerson) {
            // Initialize Lead
            $lead = $leads[$i];
            $leadId = $lead->identifier;

            // Assert a message was sent to the given leads...
            Mail::assertSent(HotPotatoEmail::class, function ($mail) use ($lead, $dealer, $location, $location2) {
                if(!empty($lead->dealer_location_id)) {
                    return $mail->hasTo($location->email);
                } elseif(!empty($lead->inventory_id)) {
                    return $mail->hasTo($location2->email);
                } else {
                    return $mail->hasTo($dealer->email);
                }
            });

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $leadId,
                'sales_person_id' => $salesPerson->getKey()
            ]);

            // Assert a lead assign entry was saved...
            $this->assertDatabaseHas('crm_lead_assign', [
                'dealer_id' => $dealerId,
                'lead_id' => $leadId,
                'chosen_salesperson_id' => $salesPerson->getKey(),
                'status' => 'mailed'
            ]);
        }
    }



    /**
     * Set Up Seeder
     * 
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make HotPotato Seeder
        $this->seeder = new HotPotatoSeeder();
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
