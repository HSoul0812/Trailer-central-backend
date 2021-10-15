<?php

namespace App\Services\CRM\Interactions;

interface InteractionServiceInterface {
    /**
     * Handle Messages From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return MessageWebhookResponse
     */
    public function message(MessageWebhookRequest $request): MessageWebhookResponse;
}