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
        $firstDate = Carbon::parseFromTimestamp($lastDate)->subDay()->toDateTimeString();

        // Use Date Submitted?
        $params = [
            'dealer_id'     => $dealer->id,
            'first_contact' => $firstDate,
            'last_contact'  => $lastDate
        ];
        if($settings->get('round-robin/hot-potato/use-submission-date')) {
            $lastCreated  = $this->datetime->subMinutes($duration)->toDateTimeString();
            $firstCreated = Carbon::parseFromTimestamp($lastCreated)->subDay()->toDateTimeString();
            $params['last_created']  = $lastCreated;
            $params['first_created'] = $firstCreated;
        }

        // Get Unprocessed Leads
        $leads = $this->leads->getAllUnprocessed($params);

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
        $this->addLeadExplanationNotes($lead->identifier, 'Found Current Assigned Sales Person: ' . $currentSalesPersonId);

        // Find Next Salesperson
        $salesPerson = $this->salesPersonRepository->roundRobinSalesPerson($dealer, $dealerLocationId, $salesType, $currentSalesPerson);
        if(empty($salesPerson->id)) {
            // Skip Entry!
            return $this->skipAssignLead($lead, $dealerLocationId, $currentSalesPersonId);
        }

        // Finish Assigning Lead and Return Result
        $this->setRoundRobinSalesPerson($dealer->id, $dealerLocationId, $lead, $salesPerson->id);
        $status = $this->handleAssignLead($lead, $salesPerson);
        $this->pushNextContactDate($lead, $settings);
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
        $this->addLeadExplanationNotes($lead->identifier, 'Sent Hot Potato Email to: ' . $salesEmail . ' for Lead: ' . $lead->id_name);
        return LeadAssign::STATUS_MAILED;
    }

    /**
     * Push Next Contact Date Back Based on Settings
     * 
     * @param Lead $lead
     * @param Collection<{key: value}> $settings
     * @return LeadStatus
     */
    private function pushNextContactDate(Lead $lead, Collection $settings): LeadStatus {
        // Set Specific Distance From Now
        $nextHr = $settings->get('round-robin/hot-potato/delay');
        $curHr  = $this->datetime->format("j");
        if($this->datetime->format("i") > $settings->get('round-robin/hot-potato/end-hour')) {
            $nextHr++;
        }

        // Increment Day if Needed
        $salesDay = $this->datetime->format("j");
        if($curHr > $settings->get('round-robin/hot-potato/end-hour')) {
            $curHr = $settings->get('round-robin/hot-potato/start-hour');
            $nextHr = 0;
            $salesDay++;
        } elseif($curHr < $settings->get('round-robin/hot-potato/start-hour')) {
            $curHr = $settings->get('round-robin/hot-potato/start-hour');
            $nextHr = 0;
        }

        // Initialize Next Contact Date
        $nextContactTime = mktime($curHr + $nextHr, 0, 0, $this->datetime->format("n"), $salesDay);
        $nextContactDate = new \DateTime(date("Y:m:d H:i:s", $nextContactTime), new \DateTimeZone($this->timezone));

        // On Weekend?
        $mon = 0;
        if(!empty($settings->get('round-robin/hot-potato/skip-weekends'))) {
            if($nextContactDate->format("N") > 5) {
                $mon = 8 - $nextContactDate->format("N");
                $salesDay += $mon;
                $nextContactTime = mktime($curHr + $nextHr, $this->datetime->format("i"), 0, $this->datetime->format("n"), $salesDay);
                $nextContactDate = new \DateTime(date("Y:m:d H:i:s", $nextContactTime), new \DateTimeZone($this->timezone));
            }
        }

        // Set Next Contact Date
        $nextContactGmt = gmdate("Y-m-d H:i:s", $nextContactTime);
        $nextContact = $nextContactDate->format("Y-m-d H:i:s");

        // Return Lead Status
        return $this->leadStatusRepository->update(['lead_id' => $lead->identifier, 'next_contact_date' => $nextContact]);
    }
}