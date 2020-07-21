<?php

namespace App\Repositories\CRM\Interactions;

use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Exceptions\CRM\Email\SendEmailFailedException;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\Lead;
use App\Repositories\Traits\SortTrait;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Carbon\Carbon;
use Throwable;

class InteractionsRepository implements InteractionsRepositoryInterface {

    /**
     * @var EmailHistoryRepositoryInterface
     */
    private $emailHistory;
    
    use SortTrait, CustomerHelper, MailHelper;
    
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
    public function __construct(EmailHistoryRepositoryInterface $emailHistory)
    {
        $this->emailHistory = $emailHistory;
    }
    
    public function create($params) {
        return Interaction::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
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
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @return Interaction || error
     */
    public function sendEmail($leadId, $params, $files) {
        // Find Lead/Sales Person
        $lead = Lead::findOrFail($leadId);
        $user = Auth::user();
        if(!empty($user->salesPerson)) {
            $this->setSalesPersonSmtpConfig($user->salesPerson);
            $fromEmail = $user->salesPerson->email;
        } else {
            $fromEmail = $user->email;
        }

        // Get Draft if Exists
        $emailHistory = $this->emailHistory->findEmailDraft($fromEmail, $lead->identifier);

        // Initialize Email Parts
        $subject = $params['subject'];
        $body = $params['body'];

        // Get Unique Message ID
        $leadProductId = $lead->getProductId();
        $uniqueId = $emailHistory->message_id ?? sprintf('<%s@%s>', $this->generateId(), $this->serverHostname());

        // Get Attachments
        $attachments = $this->emailHistory->getAttachments($files);

        // Try/Send Email!
        try {
            // Send Interaction Email
            $customer['email'] = 'david.a.conway.jr@gmail.com';
            Mail::to($customer["email"] ?? "" )->send(
                new InteractionEmail([
                    'date' => Carbon::now()->toDateTimeString(),
                    'replyToEmail' => $user->email ?? "",
                    'replyToName' => $user->crmUser->full_name,
                    'subject' => $subject,
                    'body' => $body,
                    'attach' => $attachments,
                    'id' => $uniqueId
                ])
            );
        } catch(\Exception $ex) {
            throw SendEmailFailedException($ex->getMessage());
        }

        // Create or Update
        $this->createOrUpdate([
            'interaction_id'    => $emailHistory ?? $emailHistory->interaction_id,
            'lead_product_id'   => $leadProductId,
            'tc_lead_id'        => $lead->identifier,
            'user_id'           => $user->crmUser->user_id,
            'interaction_type'  => "EMAIL",
            'interaction_notes' => "E-Mail Sent: {$subject}",
            'interaction_time'  => Carbon::now()->toDateTimeString(),
        ]);

        // Upload Attachments
        $this->emailHistory->uploadAttachments($files, $user->dealer_id, $uniqueId);

        // Insert Email
        return $this->emailHistory->createOrUpdate([
            'email_id'          => $emailHistory ?? $emailHistory->email_id,
            'interaction_id'    => $emailHistory->interaction_id,
            'message_id'        => $uniqueId,
            'lead_id'           => $lead->identifier,
            'to_name'           => $customer['name'],
            'to_email'          => $customer['email'],
            'from_email'        => $fromEmail,
            'from_name'         => $fromName,
            'subject'           => $subject,
            'body'              => $body,
            'use_html'          => true,
            'root_message_id'   => 0,
            'parent_message_id' => 0,
        ]);
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
