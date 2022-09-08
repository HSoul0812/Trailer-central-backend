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
    public function testRoundRobin()
    {
        // Seed Database With Auto Assign Leads
        $this->seeder->seed();

        // Given I have a collection of leads
        $leads = $this->seeder->leads;
        $sales = $this->seeder->sales;
        $dealerId = $this->seeder->dealer->getKey();


        // Based on the seeder results, we should know what sales person is assigned to who:
        $leadSalesPeople[$leads[0]->identifier] = $sales[1]->getKey();
        $leadSalesPeople[$leads[1]->identifier] = $sales[2]->getKey();
        $leadSalesPeople[$leads[2]->identifier] = $sales[1]->getKey();
        $leadSalesPeople[$leads[3]->identifier] = $sales[3]->getKey();
        $leadSalesPeople[$leads[4]->identifier] = $sales[2]->getKey();
        $leadSalesPeople[$leads[5]->identifier] = $sales[3]->getKey();
        $leadSalesPeople[$leads[6]->identifier] = $sales[2]->getKey();
        $leadSalesPeople[$leads[7]->identifier] = $sales[1]->getKey();


        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . $dealerId)->assertExitCode(0);


        // Loop Leads
        foreach($leadSalesPeople as $leadId => $salesPerson) {
            // Assert a message was sent to the given leads...
            /*Mail::assertSent(AutoAssignEmail::class, function ($mail) use ($salesPerson) {
                if(empty($salesPerson->email)) {
                    return false;
                }                
                return $mail->hasTo($salesPerson->email);
            });*/

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $leadId,
                'sales_person_id' => $salesPerson
            ]);

            // Assert a lead assign entry was saved...
            $this->assertDatabaseHas('crm_lead_assign', [
                'dealer_id' => $dealerId,
                'lead_id' => $leadId,
                'chosen_salesperson_id' => $salesPerson,
                'status' => 'mailed'
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
