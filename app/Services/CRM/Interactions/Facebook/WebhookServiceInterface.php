<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;

interface WebhookServiceInterface {
    /**
     * Handle Messages From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return MessageWebhookResponse
     */
    public function message(MessageWebhookRequest $request): MessageWebhookResponse;
}