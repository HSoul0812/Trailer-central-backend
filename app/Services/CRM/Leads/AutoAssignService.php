<?php

namespace App\Services\CRM\Leads;

use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use Illuminate\Support\Facades\Mail;
use App\Models\User\NewUser;
use Illuminate\Support\Facades\Log;
use App\Models\CRM\Leads\Lead;
use App\Mail\AutoAssignEmail;
use App\Traits\MailHelper;
use Carbon\Carbon;

class AutoAssignService implements AutoAssignServiceInterface {
    
    use MailHelper;
    
    /**     
     * @var App\Repositories\CRM\Leads\LeadRepository
     */
    protected $leadRepository;
    
    /**     
     * @var App\Repositories\CRM\User\SalesPersonRepositoryInterface
     */
    protected $salesPersonRepository;    
       
    /**     
     * @var array
     */
    private $leadExplanationNotes = [];
    
    /**
     * @var Array
     */
    protected $roundRobinSalesPeople = [];
    
    private $datetime;
    
    public function __construct(LeadRepositoryInterface $leadRepo, SalesPersonRepositoryInterface $salesPersonRepo) {
        $this->leadRepository = $leadRepo;
        $this->salesPersonRepository = $salesPersonRepo;
        
        date_default_timezone_set(env('DB_TIMEZONE'));
        
        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
    }
    
    public function autoAssign($lead) {
        $dealer = $lead->newDealerUser;
        $leadName = $lead->id_name;
        
        $this->setLeadExplanationNotes($lead->identifier, 'Checking Dealer #' . $dealer->id . ' ' . $dealer->name . ' for leads to auto assign');

        // Get Sales Type
        $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);

        $this->setLeadExplanationNotes($lead->identifier, 'Matched Lead Type ' . $lead->lead_type . ' to Sales Type ' . $salesType . ' for Lead ' . $leadName);
        Log::info("AutoAssignService matched lead type {$lead->lead_type} to sales type {$salesType} for lead {$leadName}");

        // Get Dealer Location
        $dealerLocationId = $lead->dealer_location_id;
        if(empty($dealerLocationId) && !empty($lead->inventory->dealer_location_id)) {
            $dealerLocationId = $lead->inventory->dealer_location_id;
            $this->setLeadExplanationNotes($lead->identifier, 'Preferred Location doesn\'t exist on Lead ' . $leadName . ', grabbed Inventory Location instead: ' . $dealerLocationId);
            Log::info("AutoAssignService lead {$leadName} doesn't have preferred location, found inventory location {$dealerLocationId}");
        } elseif(!empty($dealerLocationId)) {
            $this->setLeadExplanationNotes($lead->identifier, 'Got Preferred Location ID ' . $dealerLocationId . ' on Lead ' . $leadName);
            Log::info("AutoAssignService lead {$leadName} found preferred location {$dealerLocationId}");
        } else {
            $dealerLocationId = 0;
            $this->setLeadExplanationNotes($lead->identifier, 'Cannot Find Preferred Location on Lead ' . $leadName . ', only matching sales type ' . $salesType . ' instead');
            Log::info("AutoAssignService lead {$leadName} doesn't have preferred location, only matching sales type {$salesType} instead");
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
            $this->setLeadExplanationNotes($lead->identifier, 'Found Newest Assigned Sales Person: ' . $newestSalesPerson->id . ' for Dealer Location #' . $dealerLocationId . ' and Salesperson Type ' . $salesType);
            Log::info("AutoAssignService found newest sales person {$newestSalesPerson->id} for location {$dealerLocationId} and salesperson type {$salesType}");
        } else {
            $this->setLeadExplanationNotes($lead->identifier, 'Found Newest Assigned Sales Person: ' . $newestSalesPerson->id . ' for Dealer #' . $dealer->id . ' and Salesperson Type ' . $salesType);
            Log::info("AutoAssignService found newest sales person {$newestSalesPerson->id} for dealer {$dealer->id} and salesperson type {$salesType}");
        }

        // Find Next Salesperson
        $salesPerson = $this->salesPersonRepository->roundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $newestSalesPerson, $dealer->salespeopleEmails);
        $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $salesType, $salesPerson->id);

        // Skip Entry!
        if(empty($salesPerson->id)) {
            $this->setLeadExplanationNotes($lead->identifier, 'Couldn\'t Find Salesperson ID to Assign Lead #' . $leadName . ' to, skipping temporarily!');
            Log::error("AutoAssignService couldn't find next sales person for lead {$leadName}");
            $status = 'skipped';
            return;
        }
        // Process Auto Assign!
        else {
            $this->setLeadExplanationNotes($lead->identifier, 'Found Next Matching Sales Person: ' . $salesPerson->id . ' for Lead: ' . $leadName);
            Log::info("AutoAssignService found next sales person {$salesPerson->id} for lead {$leadName}");

            // Initialize Next Contact Date
            $nextDay = date("d") + 1;
            $nextContactStamp = mktime(9, 0, 0, $this->datetime->format("n"), $nextDay);
            $nextContactObj   = new \DateTime(date("Y:m:d H:i:s", $nextContactStamp), new \DateTimeZone(env('DB_TIMEZONE')));

            // Set Next Contact Date
            $nextContactGmt   = gmdate("Y-m-d H:i:s", $nextContactStamp);
            $nextContact      = $nextContactObj->format("Y-m-d H:i:s");
            $nextContactText  = ' on ' . $nextContactObj->format("l, F jS, Y") . ' at ' . $nextContactObj->format("g:i A T");
            $this->setLeadExplanationNotes($lead->identifier, 'Setting Next Contact Date: ' . $nextContact . ' to Lead: ' . $leadName);
            Log::info("AutoAssignService setting next contact date {$nextContact} for lead {$leadName}");

            // Set Salesperson to Lead
            try {
                // Prepare to Assign
                $status = 'assigning';
                $this->setLeadExplanationNotes($lead->identifier, 'Assigning Next Sales Person: ' . $salesPerson->id . ' to Lead: ' . $leadName);
                Log::info("AutoAssignService assigning next sales person {$salesPerson->id} for lead {$leadName}");
                $this->leadRepository->update([
                    'id' => $lead->identifier,
                    'sales_person_id' => $salesPerson->id,
                    'next_contact_date' => $nextContactGmt
                ]);

                // Finish Assigning
                $status = 'assigned';
                $this->setLeadExplanationNotes($lead->identifier, 'Assign Next Sales Person: ' . $salesPerson->id . ' to Lead: ' . $leadName);
                Log::info("AutoAssignService assigned next sales person {$salesPerson->id} for lead {$leadName}");

                // Send Sales Email
                if(!empty($dealer->crmUser->enable_assign_notification)) {
                    // Get Sales Person Email
                    $salesEmail = $salesPerson->email;
                    $status = 'mailing';
                    $this->setLeadExplanationNotes($lead->identifier, 'Attempting to Send Notification Email to: ' . $salesEmail . ' for Lead: ' . $leadName);
                    Log::info("AutoAssignService sending notification email to {$salesEmail} for lead {$leadName}");
                    
                    $credential = NewUser::getDealerCredential($dealer->user_id);
                    
                    // Send Email to Sales Person
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

                    // Success, Marked Mailed
                    $status = 'mailed';
                    $this->setLeadExplanationNotes($lead->identifier, 'Sent Notification Email to: ' . $salesEmail . ' for Lead: ' . $leadName);
                    Log::info("AutoAssignService sent notification email to {$salesEmail} for lead {$leadName}");
                }
            } catch(\Exception $e) {
                // Add Error
                if(empty($status)) {
                    $status = 'error';
                }
                $this->setLeadExplanationNotes($lead->identifier, 'Exception Returned! ' . $e->getMessage() . ': ' . $e->getTraceAsString());
                Log::error("AutoAssignService exception returned on update or email {$e->getMessage()}: {$e->getTraceAsString()}");
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
            'explanation' => $this->getLeadExplanationNotes($lead->identifier)
        ]);

        Log::info("AutoAssignService inserted assign notification for lead {$leadName} with status {$status}");
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
    
        
    private function setLeadExplanationNotes($leadId, $notes) {
        if (isset($this->leadExplanationNotes[$leadId])) {
            $this->leadExplanationNotes[$leadId][] = $notes;
        } else {
            $this->leadExplanationNotes[$leadId] = array_merge([], [$notes]);
        }
    }
    
    private function getLeadExplanationNotes($leadId) {
        return $this->leadExplanationNotes[$leadId];
    }

}
