<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use Illuminate\Support\Collection;

interface WebhookServiceInterface {
    /**
     * Handle Message From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return Collection<Message>
     */
    public function message(MessageWebhookRequest $request): Collection;
}