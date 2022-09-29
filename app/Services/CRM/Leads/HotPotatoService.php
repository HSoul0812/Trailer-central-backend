<?php

namespace App\Services\CRM\Leads;

use App\Mail\HotPotatoEmail;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewUser;
use App\Services\CRM\Leads\AutoAssignService;
use App\Services\CRM\Leads\HotPotatoServiceInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Traits\MailHelper;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HotPotatoService extends AutoAssignService implements HotPotatoServiceInterface {
    
    use MailHelper, DispatchesJobs;
    
    /**
     * @var App\Repositories\CRM\User\SettingsRepositoryInterface
     */
    protected $salesPersonRepository;
    
    public function __construct(
        LeadRepositoryInterface $leads,
        StatusRepositoryInterface $leadStatus,
        SalesPersonRepositoryInterface $salesPersonRepo,
        SettingsRepositoryInterface $settings
    ) {
        parent::__construct($leads, $leadStatus, $salesPersonRepo);

        // Initialize Settings Repository Interface
        $this->settings = $settings;

        // Initialize Logger
        $this->log = Log::channel('hotpotato');
    }


    /**
     * Handle Hot Potato for Dealer
     * 
     * @param NewDealerUser $dealer
     * @return Collection<Lead>
     */
    public function dealer(NewDealerUser $dealer): Collection {
        // Get Mapped Settings Collection
        $settings  = $this->settings->getByDealer($dealer->id);
        $duration  = $settings->get('round-robin/hot-potato/duration');
        $lastDate  = $this->datetime->subMinutes($duration)->toDateTimeString();
        $startDate = Carbon::parseFromTimestamp($lastDate)->subDay()->toDateTimeString();

        // Get Unprocessed Leads
        $leads = $this->leads->getAllUnprocessed([
            'dealer_id'  => $dealer->id,
            'start_date' => $startDate,
            'last_date'  => $lastDate
        ]);

        // No Leads? Skip Dealer
        if($leads->count() < 1) {
            $this->log->info("HotPotatoService skipping dealer {$dealer->id} because there are no pending leads");
            return new Collection();
        }

        // Loop Leads to Hot Potato
        $this->log->info("HotPotatoService dealer #{$dealer->id} found " . $leads->count() . " to process");
        foreach($leads as $lead) {
            $job = new HotPotatoJob($lead, $settings);
            $this->dispatch($job->onQueue('hot-potato'));
        }

        // Return Collection of Lead
        return $leads;
    }

    /**
     * Handle Hot Potato for Lead
     * 
     * @param Lead $lead
     * @param Collection<array{key: value}> $settings
     * @return null|LeadAssign
     */
    public function hotPotato(Lead $lead, Collection $settings): ?LeadAssign {
        // Initialize Comments
        $dealer = $lead->newDealerUser;
        $this->addLeadExplanationNotes($lead->identifier, 'Checking Lead #' . $lead->identifier . ' and Dealer #' . $dealer->id . ' ' . $dealer->name . ' to Hot Potato');

        // Get Sales Type
        $salesType = $this->salesPersonRepository->findSalesType($lead->lead_type);
        $this->addLeadExplanationNotes($lead->identifier, 'Matched Lead Type ' . $lead->lead_type . ' to Sales Type ' . $salesType . ' for Lead ' . $lead->id_name);

        // Get Dealer Location
        $dealerLocationId = $this->getLeadDealerLocation($lead);

        // Get Newest Sales Person
        $currentSalesPerson = $lead->leadStatus->salesPerson;
        $currentSalesPersonId = $currentSalesPerson->id ?? 0;
        $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $lead, $currentSalesPersonId);
        if(!empty($dealerLocationId)) {
            $this->addLeadExplanationNotes($lead->identifier, 'Found Newest Assigned Sales Person: ' . $currentSalesPersonId . ' for Dealer Location #' . $dealerLocationId . ' and Salesperson Type ' . $salesType);
        } else {
            $this->addLeadExplanationNotes($lead->identifier, 'Found Newest Assigned Sales Person: ' . $currentSalesPersonId . ' for Dealer #' . $dealer->id . ' and Salesperson Type ' . $salesType);
        }

        // Find Next Salesperson
        $salesPerson = $this->salesPersonRepository->roundRobinSalesPerson($dealer, $dealerLocationId, $salesType, $currentSalesPerson);
        if(empty($salesPerson->id)) {
            // Skip Entry!
            return $this->skipAssignLead($lead, $dealerLocationId, $currentSalesPersonId);
        }

        // Finish Assigning Lead and Return Result
        $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $lead, $salesPerson->id);
        $status = $this->handleAssignLead($lead, $salesPerson);
        return $this->markAssignLead($lead, $dealerLocationId, $currentSalesPerson, $salesPerson, $status);
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
        $this->addLeadExplanationNotes($lead->identifier, 'Attempting to Send Hot Potato Email to: ' . $salesEmail . ' for Lead: ' . $lead->id_name);
        $credential = NewUser::getDealerCredential($lead->newDealerUser->user_id, $salesPerson->id);
        $nextContactText  = ' on ' . $date->tz($lead->crmUser->dealer_timezone)->format("l, F jS, Y") .
                            ' at ' . $date->tz($lead->crmUser->dealer_timezone)->format("g:i A T");

        // Send Email to Sales Person
        Mail::to($salesEmail ?? "" )->send(
            new HotPotatoEmail([
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
}