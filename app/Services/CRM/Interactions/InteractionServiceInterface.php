<?php

namespace App\Services\CRM\Interactions;

interface InteractionServiceInterface {
    /**
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @param array $attachments
     * @return Interaction || error
     */
    public function email($leadId, $params, $attachments = array());
}