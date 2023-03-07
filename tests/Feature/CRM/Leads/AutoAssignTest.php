<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Support\Facades\Mail;
use App\Mail\AutoAssignEmail;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use Tests\database\seeds\CRM\Leads\AutoAssignSeeder;
use Tests\TestCase;

class AutoAssignTest extends TestCase
{
    /**
     * @var AutoAssignSeeder
     */
    private $seeder;


    /**
     * Test round robin only by location
     * 
     * @group CRM
     * @specs array dealer_location_id = exists
     * @specs string lead_type = inventory/trade
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testRoundRobin()
    {
        // Seed Database With Auto Assign Leads
        $this->seeder->seed();

        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $sales = $this->seeder->sales;
        $dealerId = $this->seeder->dealer->getKey();


        // Based on the seeder results, we should know what sales person is assigned to who:
        $leadSalesPeople[$leads[0]->identifier] = $sales[2];
        $leadSalesPeople[$leads[1]->identifier] = $sales[1];
        $leadSalesPeople[$leads[2]->identifier] = $sales[3];
        $leadSalesPeople[$leads[3]->identifier] = $sales[1];
        $leadSalesPeople[$leads[4]->identifier] = $sales[2];
        $leadSalesPeople[$leads[5]->identifier] = $sales[0];
        $leadSalesPeople[$leads[6]->identifier] = $sales[2];
        $leadSalesPeople[$leads[7]->identifier] = $sales[2];


        // Fake Mail
        Mail::fake();
        
        sleep(10);

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . $dealerId)->assertExitCode(0);


        // Loop Leads
        foreach($leadSalesPeople as $leadId => $salesPerson) {
            // Assert a message was sent to the given leads...
            Mail::assertSent(AutoAssignEmail::class, function ($mail) use ($salesPerson) {
                if(empty($salesPerson->email)) {
                    return false;
                }                
                return $mail->hasTo($salesPerson->email);
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
     * Test round robin with some no matches
     * 
     * @group CRM
     * @specs array dealer_location_id = exists
     * @specs string lead_type = inventory/trade
     * @specs bool enable_assign_notification = 1
     * @return void
     */
    public function testNoMatches()
    {
        // Seed Database With Auto Assign Leads
        $this->seeder->seedNoMatches();

        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $sales = $this->seeder->sales;
        $dealerId = $this->seeder->dealer->getKey();


        // Based on the seeder results, we should know what sales person is assigned to who:
        $leadSalesPeople[$leads[0]->identifier] = $sales[2];
        $leadSalesPeople[$leads[1]->identifier] = $sales[1];
        $leadSalesPeople[$leads[2]->identifier] = $sales[3];
        $leadSalesPeople[$leads[3]->identifier] = null;
        $leadSalesPeople[$leads[4]->identifier] = $sales[2];
        $leadSalesPeople[$leads[5]->identifier] = $sales[0];
        $leadSalesPeople[$leads[6]->identifier] = null;
        $leadSalesPeople[$leads[7]->identifier] = $sales[2];


        // Fake Mail
        Mail::fake();
        
        sleep(10);

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . $dealerId)->assertExitCode(0);


        // Loop Leads
        foreach($leadSalesPeople as $leadId => $salesPerson) {
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
     * Test round robin without sending email
     * 
     * @group CRM
     * @specs array dealer_location_id = exists
     * @specs string lead_type = general
     * @specs bool enable_assign_notification = 0
     * @return void
     */
    public function testNoEmail()
    {
        // Seed Database With Auto Assign Leads
        $this->seeder->seed();
        $this->seeder->enableEmail(0);

        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $sales = $this->seeder->sales;
        $dealerId = $this->seeder->dealer->getKey();


        // Based on the seeder results, we should know what sales person is assigned to who:
        $leadSalesPeople[$leads[0]->identifier] = $sales[2];
        $leadSalesPeople[$leads[1]->identifier] = $sales[1];
        $leadSalesPeople[$leads[2]->identifier] = $sales[3];
        $leadSalesPeople[$leads[3]->identifier] = $sales[1];
        $leadSalesPeople[$leads[4]->identifier] = $sales[2];
        $leadSalesPeople[$leads[5]->identifier] = $sales[0];
        $leadSalesPeople[$leads[6]->identifier] = $sales[2];
        $leadSalesPeople[$leads[7]->identifier] = $sales[2];


        // Fake Mail
        Mail::fake();
        
        sleep(10);

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . $dealerId)->assertExitCode(0);


        // Loop Leads
        foreach($leadSalesPeople as $leadId => $salesPerson) {
            // Assert a message was sent to the given leads...
            Mail::assertNotSent(AutoAssignEmail::class, function ($mail) use ($salesPerson) {
                if(empty($salesPerson->email)) {
                    return false;
                }                
                return $mail->hasTo($salesPerson->email);
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
                'status' => 'assigned'
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

        // Make AutoAssign Seeder
        $this->seeder = new AutoAssignSeeder();
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
