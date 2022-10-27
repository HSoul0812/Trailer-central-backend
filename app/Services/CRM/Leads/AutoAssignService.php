<?php

namespace App\Services\CRM\Leads;

use App\Mail\AutoAssignEmail;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use App\Models\User\NewUser;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Traits\MailHelper;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class AutoAssignService implements AutoAssignServiceInterface {
    
    use MailHelper;
    
    /**
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    protected $leads;
    
    /**
     * @var App\Repositories\CRM\Leads\StatusRepositoryInterface
     */
    protected $leadStatus;
    
    /**
     * @var App\Repositories\CRM\User\SalesPersonRepositoryInterface
     */
    protected $salesPersonRepository;
       
    /**
     * @var array
     */
    protected $leadExplanationNotes = [];
    
    /**
     * @var Array
     */
    protected $roundRobinSalesPeople = [];

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;

    /**
     * @var \DateTime
     */
    protected $datetime;
    
    public function __construct(
        LeadRepositoryInterface $leads,
        StatusRepositoryInterface $leadStatus,
        SalesPersonRepositoryInterface $salesPersonRepo
    ) {
        // Initialize Repositories Needed for Hot Potato
        $this->leads = $leads;
        $this->leadStatus = $leadStatus;
        $this->salesPersonRepository = $salesPersonRepo;

        // Set Default Date/Time With Timezone
        date_default_timezone_set(config('app.db_timezone'));
        $this->datetime = CarbonImmutable::now()->timezone(config('app.db_timezone'));

        // Initialize Logger
        $this->log = Log::channel('autoassign');
        $this->log->info('Started AutoAssignService at ' . $this->datetime->toDateTimeString() . ' using timezone ' . config('app.db_timezone'));
    }


    /**
     * Handle Auto Assign for Dealer
     * 
     * @param NewDealerUser $dealer
     * @return Collection<LeadAssign>
     */
    public function dealer(NewDealerUser $dealer): Collection {
        // Get Unassigned Leads
        $leads = $this->leads->getAllUnassigned([
            'per_page' => 'all',
            'dealer_id' => $dealer->id
        ]);

        // No Leads? Skip Dealer
        $assigned = new Collection();
        if($leads->count() < 1) {
            $this->log->info("AutoAssignService skipping dealer {$dealer->id} because there are no pending leads");
            return new $assigned;
        }

        // Loop Leads to Auto Assign
        $this->log->info("AutoAssignService dealer #{$dealer->id} found " . $leads->count() . " to process");
        foreach($leads as $lead) {                    
            $assign = $this->autoAssign($lead);
            if(!empty($assign->id)) {
                $assigned->push($assign);
            }
        }

        // Return Collection of LeadAssign
        return $assigned;
    }

    /**
     * Handle Auto Assign for Lead
     * 
     * @param Lead $lead
     * @return null|LeadAssign
     */
    public function autoAssign(Lead $lead): ?LeadAssign {
        // Initialize Comments
        $dealer = $lead->newDealerUser;
        $this->addLeadExplanationNotes($lead->identifier, 'Checking Lead #' . $lead->identifier . ' and Dealer #' . $dealer->id . ' ' . $dealer->name . ' to Auto Assign');

        // Get Sales Type
        $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);
        $this->addLeadExplanationNotes($lead->identifier, 'Matched Lead Type ' . $lead->lead_type . ' to Sales Type ' . $salesType . ' for Lead ' . $lead->id_name);

        // Get Dealer Location
        $dealerLocationId = $this->getLeadDealerLocation($lead);

        // Get Newest Sales Person
        $newestSalesPerson = $this->getNewestSalesPerson($lead, $dealerLocationId);
        $newestSalesPersonId = $newestSalesPerson->id ?? 0;
        $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $lead, $newestSalesPersonId);
        if(!empty($dealerLocationId)) {
            $this->addLeadExplanationNotes($lead->identifier, 'Found Newest Assigned Sales Person: ' . $newestSalesPersonId . ' for Dealer Location #' . $dealerLocationId . ' and Salesperson Type ' . $salesType);
        } else {
            $this->addLeadExplanationNotes($lead->identifier, 'Found Newest Assigned Sales Person: ' . $newestSalesPersonId . ' for Dealer #' . $dealer->id . ' and Salesperson Type ' . $salesType);
        }

        // Find Next Salesperson
        $salesPerson = $this->salesPersonRepository->roundRobinSalesPerson($dealer, $dealerLocationId, $salesType, $newestSalesPerson);
        if(empty($salesPerson->id)) {
            // Skip Entry!
            return $this->skipAssignLead($lead, $dealerLocationId, $newestSalesPersonId);
        }

        // Finish Assigning Lead and Return Result
        $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $lead, $salesPerson->id);
        $date = Carbon::now()->timezone($lead->crmUser->dealer_timezone)
                      ->addDay()->hour(9)->minute(0)->second(0);
        $status = $this->handleAssignLead($lead, $salesPerson, $date);
        return $this->markAssignLead($lead, $dealerLocationId, $newestSalesPerson, $salesPerson, $status);
    }


    /**
     * Get Dealer Location By Lead
     * 
     * @param Lead $lead
     * @return int
     */
    protected function getLeadDealerLocation(Lead $lead): int {
        // Initialize Getting Lead's Dealer Location
        $dealerLocationId = $lead->dealer_location_id;
        $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);

        // Get Inventory Dealer Location
        if(empty($dealerLocationId) && !empty($lead->inventory->dealer_location_id)) {
            $dealerLocationId = $lead->inventory->dealer_location_id;
            $this->addLeadExplanationNotes($lead->identifier,
                'Preferred Location doesn\'t exist on Lead ' . $lead->id_name .
                ', grabbed Inventory Location instead: ' . $dealerLocationId);
        }
        // Get Lead Dealer Location
        elseif(!empty($dealerLocationId)) {
            $this->addLeadExplanationNotes($lead->identifier, 'Got Preferred Location ID ' .
                $dealerLocationId . ' on Lead ' . $lead->id_name);
        }
        // No Lead Location
        else {
            $dealerLocationId = 0;
            $this->addLeadExplanationNotes($lead->identifier,
                'Cannot Find Preferred Location on Lead ' . $lead->id_name .
                ', only matching sales type ' . $salesType . ' instead');
        }

        // Return Corrected Dealer Location
        return $dealerLocationId;
    }

    /**
     * Get Newest Sales Person
     * 
     * @param Lead $lead
     * @param int $dealerLocationId
     * @return null|SalesPerson
     */
    protected function getNewestSalesPerson(Lead $lead, int $dealerLocationId): ?SalesPerson {
        // Initialize
        $newestSalesPerson = null;
        $dealerId = $lead->newDealerUser->id;
        $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);

        // Last Sales Person Already Exists?
        if(!empty($this->roundRobinSalesPeople[$dealerId][$dealerLocationId][$salesType])) {
            $newestSalesPersonId = $this->roundRobinSalesPeople[$dealerId][$dealerLocationId][$salesType];
            $newestSalesPerson = $this->salesPersonRepository->get([
                'sales_person_id' => $newestSalesPersonId
            ]);
        }

        // Newest Sales Person Doesn't Exist?
        if(empty($newestSalesPerson->id)) {
            $newestSalesPerson = $this->salesPersonRepository->findNewestSalesPerson($dealerId, $dealerLocationId, $salesType);
        }

        // Return Newest Sales Person
        return $newestSalesPerson;
    }


    /**
     * Preserve the Round Robin Sales Person Temporarily
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param Lead $lead
     * @param int $salesPersonId
     * @return int last sales person ID
     */
    protected function setRoundRobinSalesPerson(int $dealerId, int $dealerLocationId, Lead $lead, int $salesPersonId): int {
        // Initialize
        $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);

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


    /**
     * Prepare Assigning Lead
     * 
     * @param Lead $lead
     * @param SalesPerson $salesPerson
     * @param Carbon $date
     * @return string status of assign
     */
    protected function handleAssignLead(Lead $lead, SalesPerson $salesPerson, Carbon $date): string {
        // Try Processing Assign Lead
        $this->addLeadExplanationNotes($lead->identifier, 'Found Next Matching Sales Person: ' . $salesPerson->id . ' for Lead: ' . $lead->id_name);
        try {
            // Prepare to Assign
            $status = LeadAssign::STATUS_ASSIGNING;
            $status = $this->finishAssignLead($lead, $salesPerson, $date);

            // Send Sales Email
            if(!empty($lead->crmUser->enable_assign_notification)) {
                $status = LeadAssign::STATUS_MAILING;
                $status = $this->sendAssignLeadEmail($lead, $salesPerson, $date);
            }
        } catch(\Exception $e) {
            // Add Error
            if(empty($status)) {
                $status = 'error';
            }
            $this->addLeadExplanationNotes($lead->identifier, 'Exception Returned! ' . $e->getMessage() . ': ' . $e->getTraceAsString());
            $this->log->error("AutoAssignService exception returned on update or email {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Mark Lead as Assign
        return $status;
    }

    /**
     * Finish Assigning Lead
     * 
     * @param Lead $lead
     * @param SalesPerson $salesPerson
     * @param Carbon $date
     * @return string
     */
    protected function finishAssignLead(
        Lead $lead,
        SalesPerson $salesPerson,
        Carbon $date
    ): string {
        // Set Next Contact Date
        $nextContact = $date->format("Y-m-d H:i:s");
        $this->addLeadExplanationNotes($lead->identifier, 'Setting Next Contact Date: ' . $nextContact . ' to Lead: ' . $lead->id_name);

        // Assign Lead to Sales Person
        $this->addLeadExplanationNotes($lead->identifier, 'Assigning Next Sales Person: ' . $salesPerson->id . ' to Lead: ' . $lead->id_name);
        $this->leadStatus->createOrUpdate([
            'lead_id' => $lead->identifier,
            'sales_person_id' => $salesPerson->id,
            'next_contact_date' => $date->setTimezone($lead->crmUser->dealer_timezone)->toDateTimeString()
        ]);

        // Finish Assigning
        $this->addLeadExplanationNotes($lead->identifier, 'Assigned Next Sales Person: ' . $salesPerson->id . ' to Lead: ' . $lead->id_name);
        return LeadAssign::STATUS_ASSIGNED;
    }

    /**
     * Send Assign Lead Email
     * 
     * @param Lead $lead
     * @param SalesPerson $salesPerson
     * @param Carbon Date
     * @return string
     */
    protected function sendAssignLeadEmail(
        Lead $lead,
        SalesPerson $salesPerson,
        Carbon $date
    ): string {
        // Get Sales Person Email
        $salesEmail = $salesPerson->email;
        $this->addLeadExplanationNotes($lead->identifier, 'Attempting to Send Auto Assign Email to: ' . $salesEmail . ' for Lead: ' . $lead->id_name);
        $credential = NewUser::getDealerCredential($lead->newDealerUser->user_id, $salesPerson->id);
        $nextContactText  = ' on ' . $date->tz($lead->crmUser->dealer_timezone)->format("l, F jS, Y") .
                            ' at ' . $date->tz($lead->crmUser->dealer_timezone)->format("g:i A T");

        // Send Email to Sales Person
        Mail::to($salesEmail ?? "" )->send(
            new AutoAssignEmail([
                'date' => Carbon::now()->toDateTimeString(),
                'salesperson_name' => $salesPerson->getFullNameAttribute(),
                'launch_url' => Lead::getLeadCrmUrl($lead->identifier, $credential),
                'lead_name' => $lead->id_name,
                'lead_email' => $lead->email_address,
                'lead_phone' => $lead->phone_number,
                'lead_address' => $lead->full_address,
                'lead_status' => !empty($lead->leadStatus->status) ? $lead->leadStatus->status : LeadStatus::STATUS_UNCONTACTED,
                'lead_comments' => $lead->comments,
                'next_contact_date' => $nextContactText
            ])
        );

        // Success, Marked Mailed
        $this->addLeadExplanationNotes($lead->identifier, 'Sent Notification Email to: ' . $salesEmail . ' for Lead: ' . $lead->id_name);
        return LeadAssign::STATUS_MAILED;
    }

    /**
     * Skip Assigning Lead
     * 
     * @param Lead $lead
     * @param int $dealerLocationId
     * @param int $newestSalesPersonId
     * @return LeadAssign
     */
    protected function skipAssignLead(
        Lead $lead,
        int $dealerLocationId,
        int $newestSalesPersonId
    ): LeadAssign {
        // Initialize
        $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);

        // Log Skipping Assigning This Lead
        $this->addLeadExplanationNotes($lead->identifier, 'Couldn\'t Find Salesperson ' .
            'ID to Assign Lead ' . $lead->id_name . ' to, skipping temporarily!', 'error');

        // Unassign Lead
        $this->leadStatus->createOrUpdate([
            'lead_id' => $lead->identifier,
            'sales_person_id' => NULL
        ]);

        // Mark as Skipped
        return $this->leads->assign([
            'dealer_id' => $lead->newDealerUser->id,
            'lead_id' => $lead->identifier,
            'dealer_location_id' => $dealerLocationId,
            'salesperson_type' => $salesType,
            'found_salesperson_id' => $newestSalesPersonId,
            'chosen_salesperson_id' => 0,
            'assigned_by' => 'autoassign',
            'status' => LeadAssign::STATUS_SKIPPED,
            'explanation' => $this->getLeadExplanationNotes($lead->identifier)
        ]);
    }

    /**
     * Mark Lead as Assigned
     * 
     * @param Lead $lead
     * @param int $dealerLocationId
     * @param null|SalesPerson $found
     * @param SalesPerson $chosen
     * @param string $status
     * @return LeadAssign
     */
    protected function markAssignLead(
        Lead $lead,
        int $dealerLocationId,
        ?SalesPerson $found,
        SalesPerson $chosen,
        string $status
    ): LeadAssign {
        // Initialize
        $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);

        // Log Details for Process
        $this->log->info("AutoAssignService inserted assign notification for lead {$lead->id_name} with status {$status}");
        return $this->leads->assign([
            'dealer_id' => $lead->newDealerUser->id,
            'lead_id' => $lead->identifier,
            'dealer_location_id' => $dealerLocationId,
            'salesperson_type' => $salesType,
            'found_salesperson_id' => $found->id ?? 0,
            'chosen_salesperson_id' => $chosen->id,
            'assigned_by' => 'autoassign',
            'status' => $status,
            'explanation' => $this->getLeadExplanationNotes($lead->identifier)
        ]);
    }

    /**
     * Add Lead Explanation Notes
     * 
     * @param int $leadId
     * @param string $notes
     * @return void
     */
    protected function addLeadExplanationNotes(int $leadId, string $notes, string $log = 'info'): void {
        if (!isset($this->leadExplanationNotes[$leadId])) {
            $this->leadExplanationNotes[$leadId] = [];
        }
        $this->leadExplanationNotes[$leadId][] = $notes;

        // Update Logs
        if($log === 'error') {
            $this->log->error("AutoAssignService: {$notes}");
        } else {
            $this->log->info("AutoAssignService: {$notes}");
        }
    }

    /**
     * Get Lead Explanation Notes
     * 
     * @param int $leadId
     * @return array<string>
     */
    protected function getLeadExplanationNotes($leadId) {
        return $this->leadExplanationNotes[$leadId];
    }
}