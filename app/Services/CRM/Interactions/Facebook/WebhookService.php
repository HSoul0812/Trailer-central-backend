<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\User;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Services\CRM\Interactions\WebhookServiceInterface;

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
        InteractionsRepositoryInterface $interactions
    ) {
        $this->interactions = $interactions;
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
