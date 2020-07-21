<?php

namespace App\Repositories\CRM\Interactions;

use Illuminate\Support\Facades\Log;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Email\Attachment;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\Lead;
use App\Models\User\NewDealerUser;
use App\Models\User\User;
use App\Repositories\Traits\SortTrait;
use Carbon\Carbon;
use Throwable;

class InteractionsRepository implements InteractionsRepositoryInterface {
    
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
    
    public function create($params) {
        throw new NotImplementedException;
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
        throw new NotImplementedException;
    }

    /**
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @return Interaction || error
     */
    public function sendEmail($leadId, $params, $files) {
        // Find Lead By ID
        $lead = Lead::findOrFail($leadId);
        $dealerId = $lead->dealer_id;

        // Get Sales Person
        $this->setSalesPersonSmtpConfig($user);

        // Get Draft if Exists
        $emailHistory = EmailHistory::getEmailDraft($user->email, $lead->identifier);

        // Initialize Email Parts
        $subject = $params['subject'];
        $body = $params['body'];

        // Get Unique Message ID
        $leadProductId = $lead->getProductId();
        $uniqueId = $emailHistory->message_id ?? sprintf('<%s@%s>', $this->generateId(), $this->serverHostname());

        // Get Attachments
        $attachments = $this->getAttachments($files);
        $this->checkAttachmentsSize($files);
        if (!empty($files) && is_array($files)) {
            foreach ($files as $file) {
                $attach[] = [
                    'path' => $file->getPathname(),
                    'as' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        // Update Interaction?
        if(!empty($emailHistory) && !empty($emailHistory->interaction_id)) {
            $this->update($emailHistory->interaction_id, [
                "interaction_notes" => "E-Mail Sent: {$subject}"
            ]);
        } else {
            // Create Interaction
            $interaction = $this->create([
                "lead_product_id"   => $leadProductId,
                "tc_lead_id"        => $lead->identifier,
                "user_id"           => $user->user_id,
                "interaction_type"  => "EMAIL",
                "interaction_notes" => "E-Mail Sent: {$subject}",
                "interaction_time"  => Carbon::now()->toDateTimeString(),
            ]);
        }
        
        try {
            $attachment = new Attachment();
            $dealer = $user->dealer();
            $customer = $this->getCustomer($user, $lead);

            $customer['email'] = 'david.a.conway.jr@gmail.com';
            Mail::to($customer["email"] ?? "" )->send(
                new InteractionEmail([
                    'date' => Carbon::now()->toDateTimeString(),
                    'replyToEmail' => $user->email ?? "",
                    'replyToName' => "{$user->crmUser->first_name} {$user->crmUser->last_name}",
                    'subject' => $subject,
                    'body' => $body,
                    'attach' => $attachments,
                    'id' => $uniqueId
                ])
            );

            $insert = [
                'interaction_id'    => $emailHistory->interaction_id,
                'message_id'        => $uniqueId,
                'lead_id'           => $lead->identifier,
                'to_name'           => $customer['name'],
                'to_email'          => $customer['email'],
                'from_email'        => $user->email,
                'from_name'         => $user->username,
                'subject'           => $request->input('subject'),
                'body'              => $request->input('body'),
                'use_html'          => true,
                'root_message_id'   => 0,
                'parent_message_id'   => 0,
            ];

            $attachment->uploadAttachments($files, $dealer, $uniqueId);
            $emailHistory->createOrUpdateEmailHistory($emailHistory, $insert);

            return $this->response->array([
                'success' => true,
                'message' => "Email sent successfully",
            ]);

        } catch (Throwable $throwable) {
            Log::error($throwable->getMessage());
            return $this->response->array([
                'error' => true,
                'message' => $throwable->getMessage()
            ])->setStatusCode(500);
        }
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
