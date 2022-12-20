<?php

namespace App\Repositories\CRM\Interactions;

use App\Exceptions\RepositoryInvalidArgumentException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Email\Attachment;
use App\Models\CRM\Email\Processed;
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
            $this->updateAttachments($fields['message_id'], $fields['attachments']);
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

        DB::transaction(function() use (&$emailHistory, $fields) {
            // Insert Attachments?
            if(!empty($fields['attachments'])) {
                $this->updateAttachments($fields['message_id'], $fields['attachments']);
            }

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
     * Update Email Attachments
     *
     * @param string $messageId
     * @param array $attachments
     * @return Attachment
     */
    public function updateAttachments($messageId, $attachments) {
        // Deleted Existing Attachments for Message ID!
        Attachment::where('message_id', $messageId)->delete();

        // Loop Attachments
        $emailAttachments = [];
        if(count($attachments) > 0) {
            foreach($attachments as $attachment) {
                $attachment['message_id'] = $messageId;
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
     * @param int $quoteId
     * @return EmailHistory
     */
    public function findEmailDraft($fromEmail, $leadId, $quoteId) {
        if (!empty($leadId)) {
            $emailHistory = EmailHistory::whereLeadId($leadId);
        }

        if (!empty($quoteId)) {
            $emailHistory = EmailHistory::whereQuoteId($quoteId);
        }

        // Return Email Draft
        return $emailHistory
            ->whereFromEmail($fromEmail)
            ->whereNotNull('draft_saved')
            ->whereNull('date_sent')
            ->first();
    }

    /**
     * Get Message ID's for Dealer
     *
     * @param int $userId
     * @return array of Message ID's
     */
    public function getMessageIds($userId) {
        // Get All Message ID's for User
        return EmailHistory::leftJoin(Interaction::getTableName(),
                                      Interaction::getTableName() . '.interaction_id', '=',
                                      EmailHistory::getTableName() . '.interaction_id')
                           ->where(Interaction::getTableName() . '.user_id', $userId)
                           ->pluck('message_id')->toArray();
    }

    /**
     * Find Message ID Anywhere
     *
     * @param int $userId
     * @param string $messageId
     * @return bool
     */
    public function findMessageId($userId, $messageId) {
        // Message ID Exists in Processed?
        $processed = Processed::where('message_id', $messageId)->get();
        if(count($processed) > 0) {
            return true;
        }

        // Message ID Exists in Email History?
        $emails = EmailHistory::leftJoin(Interaction::getTableName(),
                                         Interaction::getTableName() . '.interaction_id', '=',
                                         EmailHistory::getTableName() . '.interaction_id')
                              ->where(Interaction::getTableName() . '.user_id', $userId)
                              ->where(EmailHistory::getTableName() . '.message_id', $messageId)->get();
        if(count($emails) > 0) {
            return true;
        }

        // None Returned!
        return false;
    }


    /**
     * Get Processed Message ID's for Dealer
     *
     * @param int $userId
     * @return array of Message ID's
     */
    public function getProcessed($userId) {
        // Get All Message ID's for User
        return Processed::where('user_id', $userId)->pluck('message_id')->toArray();
    }

    /**
     * Created Processed Emails
     *
     * @param int $userId
     * @param array $messageIds
     * @return Collection of Processed
     */
    public function createProcessed($userId, $messageIds) {
        // Initialized Processed
        $processed = [];
        if(!is_array($messageIds)) {
            $messageIds = [$messageIds];
        }

        // Loop Processed
        foreach($messageIds as $messageId) {
            $processed[] = Processed::create([
                'user_id' => $userId,
                'message_id' => $messageId
            ]);
        }

        // Return Collection
        return collect($processed);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function bulkUpdate(array $params): bool
    {
        if ((empty($params['ids']) || !is_array($params['ids'])) && (empty($params['search']) || !is_array($params['search']))) {
            throw new RepositoryInvalidArgumentException('ids or search param has been missed. Params - ' . json_encode($params));
        }

        $query = EmailHistory::query();

        if (!empty($params['ids']) && is_array($params['ids'])) {
            $query->whereIn('id', $params['ids']);
            unset($params['ids']);
        }

        if (!empty($params['search']['lead_id'])) {
            $query->where('lead_id', $params['search']['lead_id']);
            unset($params['search']['lead_id']);
        }

        /** @var EmailHistory<Collection> $emailHistory */
        $emailHistory = $query->get();

        foreach ($emailHistory as $email) {
            $email->update($params);
        }

        return true;
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
                    $params[$key] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
                } elseif($value === 0) {
                    $params[$key] = NULL;
                } elseif(\is_string($value) === false) {
                    unset($params[$key]);
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
