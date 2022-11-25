<?php

namespace App\Services\CRM\Interactions;

use App\Models\CRM\Interactions\Interaction;
use App\Services\CRM\User\DTOs\EmailSettings;

interface InteractionServiceInterface {
    /**
     * Get Email Config Settings
     * 
     * @param int $dealerId
     * @param null|int $salesPersonId
     * @return EmailSettings
     */
    public function config(int $dealerId, ?int $salesPersonId = null): EmailSettings;

    /**
     * Send Email to Lead
     * 
     * @param array $params
     * @param array $attachments
     * @return Interaction
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function email(array $params, array $attachments = []): Interaction;
}