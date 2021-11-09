<?php

namespace App\Services\CRM\Interactions;

use App\Models\CRM\Interactions\Interaction;

interface InteractionServiceInterface {
    /**
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @param array $attachments
     * @return Interaction
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function email(int $leadId, array $params, array $attachments = []): Interaction;
}