<?php

namespace App\Services\CRM\Interactions;

interface InteractionEmailServiceInterface {
    /**
     * Send Email With Params
     * 
     * @param int $dealerId
     * @param array $params
     * @throws SendEmailFailedException
     */
    public function send($dealerId, $params);

    /**
     * Get Attachments
     * 
     * @param type $files
     */
    public function getAttachments($files);

    /**
     * Store Uploaded Attachments
     * 
     * @param array $files
     * @param int $dealerId
     * @param string $messageId
     * @return array of saved attachments
     */
    public function storeAttachments($files, $dealerId, $messageId);
}