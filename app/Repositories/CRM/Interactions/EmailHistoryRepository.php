<?php

namespace App\Repositories\CRM\Interactions;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Email\Attachment;
use Carbon\Carbon;

class EmailHistoryRepository implements EmailHistoryRepositoryInterface {

    private $sortOrders = [
        'date_sent' => [
            'field' => 'date_sent',
            'direction' => 'DESC'
        ],
        '-date_sent' => [
            'field' => 'date_sent',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Email History
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function create($params) {
        // Fill Report Fields
        $fields = $this->fillReportFields($params);

        // Insert Attachments?
        if(!empty($fields['attachments'])) {
            $this->createAttachments($fields['attachments']);
        }

        // Create Email History Entry
        return EmailHistory::create($fields);
    }

    /**
     * Delete Email History
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * Get Email History
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function get($params) {
        return EmailHistory::findOrFail($params['id']);
    }

    /**
     * Get All Email History
     * 
     * @param array $params
     * @return Collection EmailHistory
     */
    public function getAll($params) {
        $query = EmailHistory::where('id', '>', 0);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['interaction_id'])) {
            $query = $query->where('interaction_id', $params['interaction_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Email History
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function update($params) {
        $emailHistory = EmailHistory::findOrFail($params['id']);

        // Fill Report Fields
        $fields = $this->fillReportFields($params);

        // Insert Attachments?
        if(!empty($fields['attachments'])) {
            $this->createAttachments($fields['attachments']);
        }

        DB::transaction(function() use (&$emailHistory, $fields) {
            // Fill EmailHistory Details
            $emailHistory->fill($fields)->save();
        });

        return $emailHistory;
    }

    /**
     * Create or Update Email History
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function createOrUpdate($params) {
        // ID Exists?!
        if(isset($params['id'])) {
            $emailHistory = EmailHistory::find($params['id']);

            // Email History Exists?!
            if(!empty($emailHistory->message_id)) {
                // Update Email History
                return $this->update($params);
            }
        }

        // Create Email History
        return $this->create($params);
    }

    /**
     * Create Email Attachments
     * 
     * @param array $attachments
     * @return Attachment
     */
    public function createAttachments($attachments) {
        // Initialize Attachments Array
        $emailAttachments = array();

        // Loop Attachments
        if(count($attachments) > 0) {
            foreach($attachments as $attachment) {
                $emailAttachments[] = Attachment::create($attachment);
            }
        }

        // Return Array of Created Attachments
        return $emailAttachments;
    }

    /**
     * Find Email Draft
     * 
     * @param string $fromEmail
     * @param string $leadId
     * @return EmailHistory
     */
    public function findEmailDraft($fromEmail, $leadId) {
        // Return Email Draft
        return EmailHistory::whereLeadId($leadId)
            ->whereFromEmail($fromEmail)
            ->whereNull('date_sent')
            ->first();
    }


    /**
     * Add Sort Query
     * 
     * @param string $query
     * @param string $sort
     * @return string
     */
    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

    /**
     * Fill Report Fields
     * 
     * @param array $params
     * @return array of updated params
     */
    private function fillReportFields($params) {
        // Loop Params
        foreach ($params as $key => $value) {
            // Is a Report Field?!
            if (in_array($key, EmailHistory::REPORT_FIELDS)) {
                if ($value === 1) {
                    $params[$key] = Carbon::now()->toDateTimeString();
                } else {
                    $params[$key] = $value;
                }
            } elseif (in_array($key, EmailHistory::BOOL_FIELDS)) {
                if (!empty($value)) {
                    $params[$key] = 1;
                }
            }
        }

        // Return Results
        return $params;
    }
}
