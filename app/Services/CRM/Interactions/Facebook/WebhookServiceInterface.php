<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use App\Models\CRM\Interactions\Facebook\Message;

interface WebhookServiceInterface {
    /**
     * Handle Message From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return Message
     */
    public function message(MessageWebhookRequest $request): Message;
}