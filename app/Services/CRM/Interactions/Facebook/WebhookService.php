<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use App\Models\CRM\Interactions\Facebook\Message;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\MessageRepositoryInterface;
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
        MessageRepositoryInterface $messages,
        BusinessServiceInterface $sdk
    ) {
        $this->interactions = $interactions;
        $this->messages = $messages;
        $this->sdk = $sdk;
        $this->sdk->setAppType(BusinessService::APP_TYPE_CHAT);

        // Initialize Logger
        $this->log = Log::channel('facebook');
    }

    /**
     * Handle Message From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return Message
     */
    public function message(MessageWebhookRequest $request): Message {
        // Check Request
        $this->log->info('The following request was received by the Facebook Message Webhook: ' . print_r($request->all(), true));

        // Get Page ID
        if($request->object !== 'page') {
            throw new InvalidFacebookWebhookObjectException;
        }

        // Missing Entry?
        if(empty($request->entry)) {
            throw new MissingFacebookWebhookEntryException;
        }

        // Find Conversation ID

        // Save Message
        return $this->messages->create();
    }
}
