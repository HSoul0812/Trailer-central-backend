<?php

namespace App\Repositories\CRM\Interactions;

use App\Repositories\Repository;

interface EmailHistoryRepositoryInterface extends Repository {
    /**
     * Create or Update Email History
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function createOrUpdate($params);

    /**
     * Find Email Draft
     * 
     * @param string $fromEmail
     * @param string $leadId
     * @return EmailHistory
     */
    public function findEmailDraft($fromEmail, $leadId);

    /**
     * Get Attachments
     * 
     * @param type $files
     */
    public function getAttachments($files);

    /**
     * @param $files - mail attachment(-s)
     * @return bool | string
     */
    public function checkAttachmentsSize($files);

    /**
     * Upload Attachments 
     * 
     * @param array $files
     * @param int $dealerId
     * @param string $messageId
     * @return array of saved attachments
     */
    public function uploadAttachments($files, $dealerId, $messageId);
}