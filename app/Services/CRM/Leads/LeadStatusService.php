<?php

namespace App\Services\CRM\Leads;

use App\Mail\AutoAssignEmail;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\User\NewUser;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class LeadStatusService implements LeadStatusServiceInterface
{
    /**
     * @var StatusRepositoryInterface
     */
    protected $status;

    public function __construct(StatusRepositoryInterface $status)
    {
        $this->status = $status;
    }

    /**
     * @param array $rawParams
     * @return LeadStatus
     */
    public function create(array $rawParams): LeadStatus
    {
        $status = $this->status->create($rawParams);

        if ($status->salesPerson && $status->salesPerson->crmUser->enable_assign_notification) {
            $this->sendAssignLeadEmail($status);
        }

        return $status;
    }

    /**
     * @param array $rawParams
     * @return LeadStatus
     */
    public function update(array $rawParams): LeadStatus
    {
        $oldStatus = $this->status->find($rawParams['id']);
        $status = $this->status->update($rawParams);

        if ($status->salesPerson && $status->salesPerson->crmUser->enable_assign_notification) {
            if ($oldStatus->salesPerson && $oldStatus->sales_person_id == $status->sales_person_id) {
                return $status;
            }

            $this->sendAssignLeadEmail($status);
        }

        return $status;
    }

    /**
     * Send Assign Lead Email
     *
     * @param LeadStatus $status
     * @return void
     */
    private function sendAssignLeadEmail(LeadStatus $status): void
    {
        $salesPerson = $status->salesPerson;
        $lead = $status->lead;
        $date = Carbon::now()->timezone($salesPerson->crmUser->dealer_timezone)
            ->addDay()->hour(9)->minute(0)->second(0);

        // Get Sales Person Email
        $salesEmail = $salesPerson->email;
        $credential = NewUser::getDealerCredential($lead->newDealerUser->user_id, $salesPerson->id);
        $nextContactText  = ' on ' . $date->tz($lead->crmUser->dealer_timezone)->format("l, F jS, Y") .
            ' at ' . $date->tz($lead->crmUser->dealer_timezone)->format("g:i A T");

        // Send Email to Sales Person
        Mail::to($salesEmail ?? "")->send(
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
    }
}
