<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface TextRepositoryInterface extends Repository {
    /**
     * Stop Processing Text Repository
     * 
     * @param array $params
     * @return Stop
     */
    public function stop($params);

    /**
     * Send Text
     * 
     * @param int $leadId
     * @param string $textMessage
     * @return TextLog
     */
    public function send($leadId, $textMessage);

    /**
     * Update Status for Lead
     * 
     * @param Lead $lead
     * @return LeadStatus
     */
    public function updateLeadStatus($lead);
}