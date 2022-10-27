<?php

namespace App\Services\CRM\Leads;

use App\Jobs\CRM\Leads\HotPotatoJob;
use App\Mail\AutoAssignEmail;
use App\Mail\CRM\Leads\HotPotatoEmail;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Services\CRM\Leads\AutoAssignService;
use App\Services\CRM\Leads\HotPotatoServiceInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\CRM\User\SettingsRepositoryInterface;
use App\Traits\MailHelper;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class HotPotatoService extends AutoAssignService implements HotPotatoServiceInterface {
    
    use MailHelper, DispatchesJobs;
    
    /**
     * @var SettingsRepositoryInterface
     */
    protected $salesPersonRepository;

    /**
     * HotPotatoService Constructor
     * 
     * @param LeadRepositoryInterface $leads
     * @param StatusRepositoryInterface $leadStatus
     * @param SalesPersonRepositoryInterface $salesPersonRepo
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(
        LeadRepositoryInterface $leads,
        StatusRepositoryInterface $leadStatus,
        SalesPersonRepositoryInterface $salesPersonRepo,
        SettingsRepositoryInterface $settings
    ) {
        // Initialize Repositories Needed for Hot Potato
        $this->leads = $leads;
        $this->leadStatus = $leadStatus;
        $this->salesPersonRepository = $salesPersonRepo;
        $this->settings = $settings;

        // Set Default Date/Time With Timezone
        date_default_timezone_set(config('app.db_timezone'));
        $this->datetime = CarbonImmutable::now()->timezone(config('app.db_timezone'));

        // Initialize Logger
        $this->log = Log::channel('hotpotato');
        $this->log->info('Started HotPotatoService at ' . $this->datetime->toDateTimeString() . ' using timezone ' . config('app.db_timezone'));
    }


    /**
     * Handle Hot Potato for Dealer
     * 
     * @param NewDealerUser $dealer
     * @return DBCollection<Lead>
     */
    public function dealer(NewDealerUser $dealer): DBCollection {
        // Get Mapped Settings Collection
        $settings   = $this->settings->getByDealer($dealer->id);
        $duration   = $settings->get('round-robin/hot-potato/duration');
        $lastDate   = $this->datetime->subMinutes($duration)->toDateTimeString();
        $parsedDate = new Carbon($lastDate, config('app.db_timezone'));
        $firstDate  = $parsedDate->subDay()->toDateTimeString();

        // Use Date Submitted?
        $params = [
            'dealer_id'     => $dealer->id,
            'first_contact' => $firstDate,
            'last_contact'  => $lastDate
        ];
        if($settings->get('round-robin/hot-potato/use-submission-date')) {
            $params['last_created']  = $lastDate;
            $params['first_created'] = $firstDate;
        }

        // Get Unprocessed Leads
        $this->log->info("HotPotatoService finding leads between {$firstDate} and {$lastDate}");
        $leads = $this->leads->getAllUnprocessed($params);

        // No Leads? Skip Dealer
        if($leads->count() < 1) {
            $this->log->info("HotPotatoService skipping dealer {$dealer->id} because there are no pending leads");
            return new DBCollection();
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
        $oldContactDate = $lead->leadStatus->next_contact_date;
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
        $next = $this->calcNextContactDate($lead, $settings);
        $status = $this->handleAssignLead($lead, $salesPerson, $next['next_contact_date']);
        $this->sendHotPotatoEmail($lead, $currentSalesPerson, $salesPerson, $oldContactDate, $next['weekday']);
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
        Mail::to($salesEmail)->send(
            new AutoAssignEmail([
                'date' => Carbon::now()->tz($lead->crmUser->dealer_timezone)->toDateTimeString(),
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
     * Calculate Next Contact Date Back Based on Settings
     * 
     * @param Lead $lead
     * @param Collection<{key: value}> $settings
     * @return array{next_contact_date: string,
     *               weekday: null|int}
     */
    private function calcNextContactDate(Lead $lead, Collection $settings): array {
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
        $nextContact = Carbon::create($this->datetime->format("Y"), $this->datetime->format("n"), $salesDay,
                                      $curHr + $nextHr, $this->datetime->format("i"), 0, $lead->crmUser->dealer_timezone);

        // On Weekend?
        $weekday = 0;
        if($settings->get('round-robin/hot-potato/skip-weekends') && $nextContact->format("N") > 5) {
            $weekday = $nextContact->format("N");
            $salesDay += (8 - $weekday);
            $nextContact = Carbon::create($this->datetime->format("Y"), $this->datetime->format("n"), $salesDay,
                                          $curHr + $nextHr, $this->datetime->format("i"), 0, $lead->crmUser->dealer_timezone);
        }

        // Set Next Contact Date
        $this->log->info("HotPotatoService setting lead #{$lead->identifier} next contact date to " . $nextContact->toDateTimeString());
        return ['next_contact_date' => $nextContact, 'weekday' => $weekday];
    }

    /**
     * Prepare Hot Potato Email
     * 
     * @param Lead $lead
     * @param SalesPerson $currentSalesPerson
     * @param SalesPerson $salesPerson
     * @param string $oldContactDate
     * @param null|int $weekday
     * @return string status of assign
     */
    protected function sendHotPotatoEmail(
        Lead $lead,
        SalesPerson $currentSalesPerson,
        SalesPerson $salesPerson,
        string $oldContactDate,
        ?int $weekday = 0
    ): void {
        // Configure Old Contact Date
        $oldDate = Carbon::parse($oldContactDate)->timezone($lead->crmUser->dealer_timezone);
        $oldContactText  = ' on ' . $oldDate->format("l, F jS, Y") . ' at ' . $oldDate->format("g:i A T");

        // Initialize Next Contact Date
        $date = Carbon::parse($lead->leadStatus->next_contact_date)->timezone($lead->crmUser->dealer_timezone);
        $credential = NewUser::getDealerCredential($lead->newDealerUser->user_id, $salesPerson->id);
        $nextContactText  = $date->format("l, F jS, Y") . ' at ' . $date->format("g:i A T");

        // Try Processing Admin Email
        $this->addLeadExplanationNotes($lead->identifier, 'Sending Admin Email to: ' . print_r($lead->dealer_emails, true));
        try {
            // Send Admin Email
            Mail::to($lead->dealer_emails)->send(
                new HotPotatoEmail([
                    'date' => Carbon::now()->toDateTimeString(),
                    'salesperson_name' => $currentSalesPerson->getFullNameAttribute(),
                    'new_salesperson_name' => $salesPerson->getFullNameAttribute(),
                    'launch_url' => Lead::getLeadCrmUrl($lead->identifier, $credential),
                    'lead_name' => $lead->id_name,
                    'old_contact_date' => $oldContactText,
                    'next_contact_date' => $nextContactText,
                    'weekday' => $weekday
                ])
            );
        } catch(\Exception $e) {
            // Add Error
            $this->addLeadExplanationNotes($lead->identifier, 'Exception Returned! ' . $e->getMessage() . ': ' . $e->getTraceAsString());
            $this->log->error("HotPotatoService exception returned on admin email {$e->getMessage()}: {$e->getTraceAsString()}");
        }
    }
}