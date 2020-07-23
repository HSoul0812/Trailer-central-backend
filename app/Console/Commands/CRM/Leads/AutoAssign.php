<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Models\CRM\Leads\Lead;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Traits\MailHelper;
use App\Mail\AutoAssignEmail;
use Carbon\Carbon;

class AutoAssign extends Command
{
    use MailHelper;

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
     * @var datetime
     */
    protected $datetime = null;

    /**
     * @var Array
     */
    protected $roundRobinSalesPeople = [];

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

        // Initialize Time
        date_default_timezone_set(env('DB_TIMEZONE'));
        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));

        // Get Dealers With Valid Salespeople
        $dealers = NewDealerUser::has('crmUser')->has('salespeopleEmails')->with('crmUser')->with('salespeopleEmails')->get();
        foreach($dealers as $dealer) {
            // Get Unassigned Leads
            $leads = $this->leadRepository->getAllUnassigned([
                'per_page' => 'all',
                'dealer_id' => $dealer->id
            ]);
            if(count($leads) < 1) {
                continue;
            }

            // Get Dealer Credential
            $credential = NewUser::getDealerCredential($dealer->user_id);

            // Create Dealer Notes
            $dealerNotes = array();
            $dealerNotes[] = 'Checking Dealer #' . $dealer->id . ' ' . $dealer->name . ' for leads to auto assign';
            $dealerNotes[] = 'Found ' . count($leads) . ' total leads for Dealer ID #' . $dealer->id;

            // Loop Leads for Current Dealer
            foreach($leads as $lead) {
                // Initialize Notes Array
                $notes = $dealerNotes;
                $leadName = $lead->getIdNameAttribute();

                // Get Sales Type
                $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);
                $notes[] = 'Matched Lead Type ' . $lead->lead_type . ' to Sales Type ' . $salesType . ' for Lead ' . $leadName;

                // Get Dealer Location
                $dealerLocationId = $lead->dealer_location_id;
                if(empty($dealerLocationId) && !empty($lead->inventory->dealer_location_id)) {
                    $dealerLocationId = $lead->inventory->dealer_location_id;
                    $notes[] = 'Preferred Location doesn\'t exist on Lead ' . $leadName . ', grabbed Inventory Location instead: ' . $dealerLocationId;
                } elseif(!empty($dealerLocationId)) {
                    $notes[] = 'Got Preferred Location ID ' . $dealerLocationId . ' on Lead ' . $leadName;
                } else {
                    $dealerLocationId = 0;
                    $notes[] = 'Cannot Find Preferred Location on Lead ' . $leadName . ', ignoring Dealer Location in Matching';
                }

                // Last Sales Person Already Exists?
                $newestSalesPerson = null;
                if(isset($this->roundRobinSalesPeople[$dealer->id][$dealerLocationId][$salesType])) {
                    $newestSalesPersonId = $this->roundRobinSalesPeople[$dealer->id][$dealerLocationId][$salesType];
                    $newestSalesPerson = $this->salesPersonRepository->get([
                        'sales_person_id' => $newestSalesPersonId
                    ]);
                }

                // Newest Sales Person DOESN'T Exist?
                if(empty($newestSalesPerson->id)) {
                    // Look it up!
                    $newestSalesPerson = $this->salesPersonRepository->findNewestSalesPerson($dealer->id, $dealerLocationId, $salesType);
                    $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $newestSalesPerson->id);
                }
                if(!empty($dealerLocationId)) {
                    $notes[] = 'Found Newest Assigned Sales Person: ' . $newestSalesPerson->id . ' for Dealer Location #' . $dealerLocationId . ' and Salesperson Type ' . $salesType;
                } else {
                    $notes[] = 'Found Newest Assigned Sales Person: ' . $newestSalesPerson->id . ' for Dealer #' . $dealer->id . ' and Salesperson Type ' . $salesType;
                }

                // Find Next Salesperson
                $salesPerson = $this->salesPersonRepository->roundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
                $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $salesPerson->id);

                // Skip Entry!
                if(empty($salesPerson->id)) {
                    $notes[] = 'Couldn\'t Find Salesperson ID to Assign Lead #' . $lead->identifier . ' to, skipping temporarily!';
                    $status = 'skipped';
                    continue;
                }
                // Process Auto Assign!
                else {
                    $notes[] = 'Found Next Matching Sales Person: ' . $newestSalesPerson->id . ' for Lead: ' . $leadName;

                    // Initialize Next Contact Date
                    $nextDay = date("d") + 1;
                    $nextContactStamp = mktime(9, 0, 0, $this->datetime->format("n"), $nextDay);
                    $nextContactObj   = new \DateTime(date("Y:m:d H:i:s", $nextContactStamp), new \DateTimeZone(env('DB_TIMEZONE')));

                    // Set Next Contact Date
                    $nextContactGmt   = gmdate("Y-m-d H:i:s", $nextContactStamp);
                    $nextContact      = $nextContactObj->format("Y-m-d H:i:s");
                    $nextContactText  = ' on ' . $nextContactObj->format("l, F jS, Y") . ' at ' . $nextContactObj->format("g:i A T");
                    $notes[] = 'Setting Next Contact Date: ' . $nextContact . ' to Lead: ' . $leadName;

                    // Set Salesperson to Lead
                    try {
                        $this->leadRepository->update([
                            'id' => $lead->identifier,
                            'sales_person_id' => $salesPerson->id,
                            'next_contact_date' => $nextContactGmt
                        ]);
                        $status = 'assigned';
                        $notes[] = 'Assign Next Sales Person: ' . $salesPerson->id . ' to Lead: ' . $leadName;

                        // Send Sales Email
                        if(!empty($dealer->crmUser->enable_assign_notification)) {
                            // Send Email to Sales Person
                            $status = 'mailed';
                            $salesEmail = $salesPerson->email;
                            Mail::to($salesEmail ?? "" )->send(
                                new AutoAssignEmail([
                                    'date' => Carbon::now()->toDateTimeString(),
                                    'salesperson_name' => $salesPerson->getFullNameAttribute(),
                                    'launch_url' => Lead::getLeadCrmUrl($lead->identifier, $credential),
                                    'lead_name' => $leadName,
                                    'lead_email' => $lead->email_address,
                                    'lead_phone' => $lead->phone_number,
                                    'lead_address' => $lead->getFullAddressAttribute(),
                                    'lead_status' => !empty($lead->leadStatus->status) ? $lead->leadStatus->status : 'Uncontacted',
                                    'lead_comments' => $lead->comments,
                                    'next_contact_date' => $nextContactText,
                                    'id' => sprintf('<%s@%s>', $this->generateId(), $this->serverHostname())
                                ])
                            );
                            $notes[] = 'Sent Notification Email to: ' . $salesEmail . ' for Lead: ' . $leadName;
                        }
                    } catch(\Exception $e) {
                        // Add Error
                        if(empty($status)) {
                            $status = 'error';
                        }
                        $notes[] = 'Exception Returned! ' . $e->getMessage() . ': ' . $e->getTraceAsString();
                    }
                }

                // Log Details for Process
                $this->leadRepository->assign([
                    'dealer_id' => $dealer->id,
                    'lead_id' => $lead->identifier,
                    'dealer_location_id' => $dealerLocationId,
                    'salesperson_type' => $salesType,
                    'found_salesperson_id' => $newestSalesPerson->id,
                    'chosen_salesperson_id' => $salesPerson->id,
                    'assigned_by' => 'autoassign',
                    'status' => $status,
                    'explanation' => $notes
                ]);
            }
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
    public function setRoundRobinSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPersonId) {
        // Assign to Arrays
        if(!isset($this->roundRobinSalesPeople[$dealerId])) {
            $this->roundRobinSalesPeople[$dealerId] = array();
        }

        // Match By Dealer Location ID!
        if(!empty($dealerLocationId)) {
            if(!isset($this->roundRobinSalesPeople[$dealerId][$dealerLocationId])) {
                $this->roundRobinSalesPeople[$dealerId][$dealerLocationId] = array();
            }
            $this->roundRobinSalesPeople[$dealerId][$dealerLocationId][$salesType] = $salesPersonId;
        }

        // Always Set for 0!
        if(!isset($this->roundRobinSalesPeople[$dealerId][0])) {
            $this->roundRobinSalesPeople[$dealerId][0] = array();
        }
        $this->roundRobinSalesPeople[$dealerId][0][$salesType] = $salesPersonId;

        // Return Last Sales Person ID
        return $this->roundRobinSalesPeople[$dealerId][0][$salesType];
    }
}