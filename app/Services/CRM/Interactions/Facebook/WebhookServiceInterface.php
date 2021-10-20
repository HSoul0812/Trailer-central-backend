<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;

interface WebhookServiceInterface {
    /**
     * @const Valid Response to Facebook
     */
    const VALID_RESPONSE = 'EVENT_RECEIVED';


    /**
     * Handle Message From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return bool | true = messages posted successfull, false = no messages were sent
     */
    public function message(MessageWebhookRequest $request): bool;
}