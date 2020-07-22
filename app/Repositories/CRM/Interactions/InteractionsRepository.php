<?php

namespace App\Repositories\CRM\Interactions;

use Illuminate\Support\Facades\Auth;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\Lead;
use App\Repositories\Traits\SortTrait;
use Carbon\Carbon;
use Throwable;

class InteractionsRepository implements InteractionsRepositoryInterface {

    /**
     * @var InteractionEmailServiceInterface
     */
    private $interactionEmail;

    /**
     * @var EmailHistoryRepositoryInterface
     */
    private $emailHistory;
    
    use SortTrait;
    
    private $sortOrders = [
        'created_at' => [
            'field' => 'interaction_time',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'interaction_time',
            'direction' => 'ASC'
        ]
    ];
    
    private $sortOrdersNames = [
        'created_at' => [
            'name' => 'Newest Tasks to Oldest Tasks, then Future Tasks',
        ],
        '-created_at' => [
            'name' => 'Oldest Tasks to Newest Tasks, then Future Tasks',
        ],
    ];

    /**
     * InteractionsRepository constructor.
     * 
     * @param EmailHistoryRepositoryInterface
     */
    public function __construct(InteractionEmailServiceInterface $service, EmailHistoryRepositoryInterface $emailHistory)
    {
        $this->interactionEmail = $service;
        $this->emailHistory = $emailHistory;
    }
    
    public function create($params) {
        return Interaction::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Interaction::findOrFail($params['id']);
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        $interaction = Interaction::findOrFail($params['id']);

        DB::transaction(function() use (&$interaction, $params) {
            // Fill Interaction Details
            $interaction->fill($params)->save();
        });

        return $interaction;
    }

    /**
     * Create or Update Interaction
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function createOrUpdate($params) {
        // ID Exists?!
        if(isset($params['id'])) {
            $interaction = Interaction::find($params['id']);

            // Interaction Exists?!
            if(!empty($interaction->interaction_id)) {
                // Update Interaction
                return $this->update($params);
            }
        }

        // Create Interaction
        return $this->create($params);
    }

    /**
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @return Interaction || error
     */
    public function sendEmail($leadId, $params) {
        // Find Lead/Sales Person
        $lead = Lead::findOrFail($leadId);
        $user = Auth::user();
        if(!empty($user->salesPerson)) {
            $this->interactionEmail->setSalesPersonSmtpConfig($user->salesPerson);
            $params['from_email'] = $user->salesPerson->email;
            $params['from_name'] = $user->salesPerson->full_name;
        } else {
            $params['from_email'] = $user->email;
            $params['from_name'] = $user->name;
        }

        // Get Draft if Exists
        $emailHistory = $this->emailHistory->findEmailDraft($params['from_email'], $lead->identifier);
        if(!empty($emailHistory->message_id)) {
            $params['id']             = $emailHistory->email_id;
            $params['interaction_id'] = $emailHistory->interaction_id;
            $params['message_id']     = $emailHistory->message_id;
        }

        // Set Lead Details
        $params['to_email'] = $lead->email_address;
        $params['to_name']  = $lead->full_name;

        // Send Email
        $email = $this->interactionEmail->send($lead->dealer_id, $params);

        // Create or Update
        $interaction = $this->createOrUpdate([
            'id'                => $params['interaction_id'] ?? 0,
            'tc_lead_id'        => $lead->identifier,
            'user_id'           => $user->newDealerUser->user_id,
            'interaction_type'  => "EMAIL",
            'interaction_notes' => "E-Mail Sent: {$email['subject']}",
            'interaction_time'  => Carbon::now()->toDateTimeString(),
        ]);

        // Set Interaction ID!
        $email['interaction_id'] = $interaction->interaction_id;

        // Insert Email
        $this->emailHistory->createOrUpdate($email);

        // Return Interaction
        return $this->get(['id' => $email['interaction_id']]);
    }

    public function getTasksByDealerId($dealerId, $sort = '-created_at', $perPage = 15) {
        $query = Interaction::select('*');       

        $query->leftJoin(LeadStatus::getTableName(), LeadStatus::getTableName().'.tc_lead_identifier', '=', Interaction::getTableName().'.tc_lead_id');
        $query->join(Lead::getTableName(), Lead::getTableName().'.identifier', '=', Interaction::getTableName().'.tc_lead_id');
        
        $query->where(Lead::getTableName().'.dealer_id', $dealerId);
        $query->where('interaction_time', 'not like', "0000%");
        $query->whereRaw(Interaction::getTableName().'.interaction_type =' . LeadStatus::getTableName() . '.contact_type');
        
        $query = $this->addSortQuery($query, $sort);        

        return $query->paginate($perPage)->appends(['per_page' => $perPage]);        
    }
    
    public function getTasksSortFields() {
        return $this->getSortFields();
    }
    
    protected function getSortOrderNames() {
        return $this->sortOrdersNames;
    }
    
    protected function getSortOrders() {
        return $this->sortOrders;
    }

    

}
