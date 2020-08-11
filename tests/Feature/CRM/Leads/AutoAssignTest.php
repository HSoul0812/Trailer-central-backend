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
     * Test all auto assign dealers
     *
     * @return void
     */
    public function testDealer()
    {
        // Initialize Repositories
        $leadRepo = new LeadRepository();
        $salesRepo = new SalesPersonRepository();

        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Build Random Factory Salespeople
        foreach(TestCase::getTestLocationIds() as $locationId) {
            // Get Sales People By Location
            $salespeople = SalesPerson::where('user_id', $dealer->crmUser->user_id)
                                      ->where('dealer_location_id', $locationId)->get();
            if(empty($salespeople) || count($salespeople) < 1) {
                $salespeople = factory(SalesPerson::class, 3)->create([
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
        $leads = $leadRepo->getAllUnassigned(['dealer_id' => $dealer->id]);
        if(empty($leads) || count($leads) < 1) {
            // Build Random Factory Leads
            factory(Lead::class, 10)->create();
            $leads = $leadRepo->getAllUnassigned(['dealer_id' => $dealer->id]);
        }

        // Detect What Sales People Will be Assigned!
        $roundRobinSalesPeople = array();
        $leadSalesPeople = array();
        foreach($leads as $lead) {
            // Get Newest Sales Person
            $salesType = $salesRepo->findSalesType($lead->lead_type);
            $dealerLocationId = !empty($lead->dealer_location_id) ? $lead->dealer_location_id : 0;
            if(empty($dealerLocationId) && !empty($lead->inventory->dealer_location_id)) {
                $dealerLocationId = $lead->inventory->dealer_location_id;
            }

            // Find Newest Assigned Sales Person
            if(!isset($roundRobinSalesPeople[$dealer->id][$dealerLocationId][$salesType])) {
                $newestSalesPerson = $salesRepo->findNewestSalesPerson($dealer->id, $dealerLocationId, $salesType);
                if(!isset($roundRobinSalesPeople[$dealer->id])) {
                    $roundRobinSalesPeople[$dealer->id] = array();
                }
                if(!isset($roundRobinSalesPeople[$dealer->id][$dealerLocationId])) {
                    $roundRobinSalesPeople[$dealer->id][$dealerLocationId] = array();
                }
                $roundRobinSalesPeople[$dealer->id][$dealerLocationId][$salesType] = $newestSalesPerson->id;
            }
            $newestSalesPersonId = $roundRobinSalesPeople[$dealer->id][$dealerLocationId][$salesType];
            $newestSalesPerson = SalesPerson::find($newestSalesPersonId);

            // Find Next!
            $salesPerson = $salesRepo->roundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $newestSalesPerson);
            $leadSalesPeople[$lead->identifier] = !empty($salesPerson->id) ? $salesPerson->id : 0;
            $roundRobinSalesPeople[$dealer->id][$dealerLocationId][$salesType] = $leadSalesPeople[$lead->identifier];
        }

        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . self::getTestDealerId())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {
            // Assert a message was sent to the given leads...
            $salesPerson = SalesPerson::find($leadSalesPeople[$lead->identifier]);
            $status = 'assigned';
            if(!empty($dealer->crmUser->enable_assign_notification)) {
                Mail::assertSent(AutoAssignEmail::class, function ($mail) use ($salesPerson) {
                    if(empty($salesPerson->email)) {
                        return false;
                    }
                    return $mail->hasTo($salesPerson->email);
                });
                $status = 'mailed';
            }

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
                'status' => $status
            ]);
        }
    }
}
