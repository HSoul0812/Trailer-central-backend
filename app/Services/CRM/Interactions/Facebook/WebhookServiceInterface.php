<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use Illuminate\Support\Collection;

interface WebhookServiceInterface {
    /**
     * @const Valid Response to Facebook
     */
    const VALID_RESPONSE = 'EVENT_RECEIVED';


    /**
     * Handle Message From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return Collection<Message>
     */
    public function message(MessageWebhookRequest $request): Collection;
}