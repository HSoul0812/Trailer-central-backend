<?php

namespace App\Console\Commands\CRM\Text;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;

class ProcessCampaign extends Command
{
    use MailHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'text:process-campaign {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process sending texts to all leads on all active campaigns.';

    /**
     * @var App\Repositories\CRM\Leads\LeadRepository
     */
    protected $leads;

    /**
     * @var App\Repositories\CRM\Text\CampaignRepository
     */
    protected $campaigns;

    /**
     * @var datetime
     */
    protected $datetime = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LeadRepositoryInterface $leadRepo, CampaignRepositoryInterface $campaignRepo)
    {
        parent::__construct();

        $this->leads = $leadRepo;
        $this->campaigns = $campaignRepo;
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

        // Try Catching Error for Whole Script
        try {
            // Log Start
            $now = $this->datetime->format("l, F jS, Y");
            $command = "text:campaign" . (!empty($dealerId) ? ' ' . $dealerId : '');
            $this->info("{$command} started {$now}");

            // Handle Dealer Differently
            $dealers = array();
            if(!empty($dealerId)) {
                $dealer = NewDealerUser::findOrFail($dealerId);
                $dealers[] = $dealer;
            } else {
                $dealers = NewDealerUser::has('activeCrmUser')->has('salespeopleEmails')->get();
            }
            $this->info("{$command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Valid Salespeople
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
                $this->info("{$command} dealer #{$dealer->id} found " . count($leads) . " to process");

                // Loop Leads for Current Dealer
                foreach($leads as $lead) {
                    // Initialize Notes Array
                    $notes = $dealerNotes;
                    $leadName = $lead->getIdNameAttribute();

                    // Get Sales Type
                    $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);
                    $notes[] = 'Matched Lead Type ' . $lead->lead_type . ' to Sales Type ' . $salesType . ' for Lead ' . $leadName;
                    $this->info("{$command} matched lead type {$lead->lead_type} to sales type {$salesType} for lead {$leadName}");

                    // Get Dealer Location
                    $dealerLocationId = $lead->dealer_location_id;
                    if(empty($dealerLocationId) && !empty($lead->inventory->dealer_location_id)) {
                        $dealerLocationId = $lead->inventory->dealer_location_id;
                        $notes[] = 'Preferred Location doesn\'t exist on Lead ' . $leadName . ', grabbed Inventory Location instead: ' . $dealerLocationId;
                        $this->info("{$command} lead {$leadName} doesn't have preferred location, found inventory location {$dealerLocationId}");
                    } elseif(!empty($dealerLocationId)) {
                        $notes[] = 'Got Preferred Location ID ' . $dealerLocationId . ' on Lead ' . $leadName;
                        $this->info("{$command} lead {$leadName} found preferred location {$dealerLocationId}");
                    } else {
                        $dealerLocationId = 0;
                        $notes[] = 'Cannot Find Preferred Location on Lead ' . $leadName . ', only matching sales type ' . $salesType . ' instead';
                        $this->info("{$command} lead {$leadName} doesn't have preferred location, only matching sales type {$salesType} instead");
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
                        $this->info("{$command} found newest sales person {$newestSalesPerson->id} for location {$dealerLocationId} and salesperson type {$salesType}");
                    } else {
                        $notes[] = 'Found Newest Assigned Sales Person: ' . $newestSalesPerson->id . ' for Dealer #' . $dealer->id . ' and Salesperson Type ' . $salesType;
                        $this->info("{$command} found newest sales person {$newestSalesPerson->id} for dealer {$dealer->id} and salesperson type {$salesType}");
                    }

                    // Find Next Salesperson
                    $salesPerson = $this->salesPersonRepository->roundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
                    $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $salesPerson->id);

                    // Skip Entry!
                    if(empty($salesPerson->id)) {
                        $notes[] = 'Couldn\'t Find Salesperson ID to Assign Lead #' . $leadName . ' to, skipping temporarily!';
                        $this->info("{$command} couldn't find next sales person for lead {$leadName}");
                        $status = 'skipped';
                        continue;
                    }
                    // Process Auto Assign!
                    else {
                        $notes[] = 'Found Next Matching Sales Person: ' . $salesPerson->id . ' for Lead: ' . $leadName;
                        $this->info("{$command} found next sales person {$salesPerson->id} for lead {$leadName}");

                        // Initialize Next Contact Date
                        $nextDay = date("d") + 1;
                        $nextContactStamp = mktime(9, 0, 0, $this->datetime->format("n"), $nextDay);
                        $nextContactObj   = new \DateTime(date("Y:m:d H:i:s", $nextContactStamp), new \DateTimeZone(env('DB_TIMEZONE')));

                        // Set Next Contact Date
                        $nextContactGmt   = gmdate("Y-m-d H:i:s", $nextContactStamp);
                        $nextContact      = $nextContactObj->format("Y-m-d H:i:s");
                        $nextContactText  = ' on ' . $nextContactObj->format("l, F jS, Y") . ' at ' . $nextContactObj->format("g:i A T");
                        $notes[] = 'Setting Next Contact Date: ' . $nextContact . ' to Lead: ' . $leadName;
                        $this->info("{$command} setting next contact date {$nextContact} for lead {$leadName}");

                        // Set Salesperson to Lead
                        try {
                            // Prepare to Assign
                            $status = 'assigning';
                            $notes[] = 'Assigning Next Sales Person: ' . $salesPerson->id . ' to Lead: ' . $leadName;
                            $this->info("{$command} assigning next sales person {$salesPerson->id} for lead {$leadName}");
                            $this->leadRepository->update([
                                'id' => $lead->identifier,
                                'sales_person_id' => $salesPerson->id,
                                'next_contact_date' => $nextContactGmt
                            ]);

                            // Finish Assigning
                            $status = 'assigned';
                            $notes[] = 'Assign Next Sales Person: ' . $salesPerson->id . ' to Lead: ' . $leadName;
                            $this->info("{$command} assigned next sales person {$salesPerson->id} for lead {$leadName}");

                            // Send Sales Email
                            if(!empty($dealer->crmUser->enable_assign_notification)) {
                                // Get Sales Person Email
                                $salesEmail = $salesPerson->email;
                                $status = 'mailing';
                                $notes[] = 'Attempting to Send Notification Email to: ' . $salesEmail . ' for Lead: ' . $leadName;
                                $this->info("{$command} sending notification email to {$salesEmail} for lead {$leadName}");
                            }
                        } catch(\Exception $e) {
                            // Add Error
                            if(empty($status)) {
                                $status = 'error';
                            }
                            $notes[] = 'Exception Returned! ' . $e->getMessage() . ': ' . $e->getTraceAsString();
                            Log::error("{$command} exception returned on update or email {$e->getMessage()}: {$e->getTraceAsString()}");
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
                    $this->info("{$command} inserted assign notification for lead {$leadName} with status {$status}");
                }
            }
        } catch(\Exception $e) {
            Log::error("{$command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $this->info("{$command} finished on " . $datetime->format("l, F jS, Y"));
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
