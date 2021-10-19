<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Services\Integration\Facebook\BusinessService;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class WebhookService
 * 
 * @package App\Services\CRM\Interactions\Facebook
 */
class WebhookService implements WebhookServiceInterface
{
    /**
     * InteractionsRepository constructor.
     * 
     * @param InteractionsRepositoryInterface $interactions
     */
    public function __construct(
        InteractionsRepositoryInterface $interactions,
        BusinessServiceInterface $sdk
    ) {
        $this->interactions = $interactions;
        $this->sdk = $sdk;
        $this->sdk->setAppType(BusinessService::APP_TYPE_CHAT);

        // Initialize Logger
        $this->log = Log::channel('facebook');
    }

    /**
     * Handle Message From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return MessageWebhookResponse
     */
    public function message(MessageWebhookRequest $request): MessageWebhookResponse {
        // Save Email
        return $this->saveEmail($leadId, $user->newDealerUser->user_id, $finalEmail, $salesPerson, $interactionEmail);
    }
}
