<?php

namespace App\Repositories\CRM\Interactions;

use Illuminate\Support\Facades\DB;
use App\Exceptions\CRM\Email\ExceededTotalAttachmentSizeException;
use App\Exceptions\CRM\Email\ExceededSingleAttachmentSizeException;
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
        $emailHistory = EmailHistory::findOrFail($params['id']);

        // Email History Exists?!
        if(empty($emailHistory)) {
            return $this->create($params);
        }

        // Update Email History
        return $this->update($params);
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
     * Get Attachments
     * 
     * @param type $files
     */
    public function getAttachments($files) {
        // Check Size of Attachments
        $this->checkAttachmentsSize($files);
        if (!empty($files) && is_array($files)) {
            foreach ($files as $file) {
                $attachments[] = [
                    'path' => $file->getPathname(),
                    'as' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }
    }

    /**
     * @param $files - mail attachment(-s)
     * @return bool | string
     */
    public function checkAttachmentsSize($files) {
        // Calculate Total Size
        $totalSize = 0;
        foreach ($files as $file) {
            if ($file['size'] > Attachment::MAX_FILE_SIZE) {
                throw new ExceededSingleAttachmentSizeException();
            } else if ($totalSize > Attachment::MAX_UPLOAD_SIZE) {
                throw new ExceededTotalAttachmentSizeException();
            }
            $totalSize += $file['size'];
        }

        // Return Total Size
        return $totalSize;
    }

    /**
     * Upload Attachments 
     * 
     * @param array $files
     * @param int $dealerId
     * @param string $messageId
     * @return array of saved attachments
     */
    public function uploadAttachments($files, $dealerId, $messageId) {
        // Calculate Directory
        $messageDir = str_replace(">", "", str_replace("<", "", $messageId));

        // Loop Attachments
        $attachments = array();
        if (!empty($files) && is_array($files)) {
            // Valid Attachment Size?!
            if($this->checkAttachmentsSize($files)) {
                // Loop Attachments
                foreach ($files as $file) {
                    // Generate Path
                    $path_parts = pathinfo( $file->getPathname() );
                    $filePath = 'https://email-trailercentral.s3.amazonaws.com/' . 'crm/'
                        . $dealerId . "/" . $messageDir
                        . "/attachments/{$path_parts['filename']}." . $path_parts['extension'];

                    // Save File to S3
                    Storage::disk('s3')->put($filePath, file_get_contents($file));

                    // Create Attachment
                    $attachments[] = $this->createAttachment([
                        'message_id' => $messageId,
                        'filename' => $filePath,
                        'original_filename' => time() . $file->getClientOriginalName()
                    ]);
                }
            }
        }

        // Return Attachment Objects
        return $attachments;
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
