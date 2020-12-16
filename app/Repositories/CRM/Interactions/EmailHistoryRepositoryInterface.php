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
     * Create Email Attachments
     * 
     * @param array $attachments
     * @return Attachment
     */
    public function createAttachments($attachments);

    /**
     * Get Message ID's for Dealer
     * 
     * @param int $userId
     * @return array of Message ID's
     */
    public function getMessageIds($userId);

    /**
     * Get Processed Message ID's for Dealer
     * 
     * @param int $userId
     * @return array of Message ID's
     */
    public function getProcessed($userId);
}