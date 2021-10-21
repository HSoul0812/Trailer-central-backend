<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Exceptions\CRM\Interactions\Facebook\FacebookWebhookVerifyInvalidModeException;
use App\Exceptions\CRM\Interactions\Facebook\FacebookWebhookVerifyMismatchException;
use App\Exceptions\CRM\Interactions\Facebook\InvalidFacebookWebhookObjectException;
use App\Exceptions\CRM\Interactions\Facebook\MissingFacebookWebhookEntryException;
use App\Models\User\Integration\Integration;
use App\Models\User\AuthToken;
use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookVerify;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\ConversationRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\MessageRepositoryInterface;
use App\Services\CRM\Interactions\Facebook\DTOs\ChatMessage;
use App\Services\Integration\Facebook\BusinessService;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Class WebhookService
 * 
 * @package App\Services\CRM\Interactions\Facebook
 */
class WebhookService implements WebhookServiceInterface
{
    /**
     * @const Facebook Messenger Integration Name
     */
    const FBCHAT_INTEGRATION_NAME = 'facebook_messenger';


    /**
     * @var InteractionRepositoryInterface
     */
    protected $interactions;

    /**
     * @var ConversationRepositoryInterface
     */
    protected $conversations;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messages;

    /**
     * @var BusinessServiceInterface
     */
    protected $sdk;

    /**
     * InteractionsRepository constructor.
     * 
     * @param InteractionsRepositoryInterface $interactions
     * @param ConversationRepositoryInterface $conversations
     * @param MessageRepositoryInterface $messages
     * @param BusinessServiceInterface $sdk
     */
    public function __construct(
        InteractionsRepositoryInterface $interactions,
        ConversationRepositoryInterface $conversations,
        MessageRepositoryInterface $messages,
        BusinessServiceInterface $sdk
    ) {
        $this->interactions = $interactions;
        $this->conversations = $conversations;
        $this->messages = $messages;
        $this->sdk = $sdk;
        $this->sdk->setAppType(BusinessService::APP_TYPE_CHAT);

        // Initialize Logger
        $this->log = Log::channel('facebook');
    }


    /**
     * Verify Endpoint
     * 
     * @param MessageWebhookVerify $request
     * @return null|string
     */
    public function verify(MessageWebhookVerify $request): ?string {
        // Mode Exists?
        if(empty($request->hub_mode) || $request->hub_mode !== 'subscribe') {
            throw new FacebookWebhookVerifyInvalidModeException;
        }

        // Get Verify Token From DB
        $integration = Integration::where('name', self::FBCHAT_INTEGRATION_NAME)->first();

        // Get Auth Token
        $authToken = AuthToken::where('user_type', 'integration')->where('user_id', $integration->id)->first();

        // Access Token Doesn't Match?!
        if($authToken->access_token !== $request->hub_verify_token) {
            throw new FacebookWebhookVerifyMismatchException;
        }

        // Return Challenge Instead!
        return $request->hub_challenge;
    }


    /**
     * Handle Message From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return Collection<Message>
     */
    public function message(MessageWebhookRequest $request): Collection {
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

        // Loop Entries
        $messages = new Collection();
        foreach($request->entry as $entry) {
            // Get Page ID
            $pageId = $entry['id'];

            // Process Messages for Page
            foreach($entry['messaging'] as $message) {
                // Get User ID
                $userId = $message['sender']['id'] === $pageId ? $message['recipient']['id'] : $message['sender']['id'];

                // Find Conversation ID
                $conversation = $this->conversations->getByParticipants($pageId, $userId);
                $this->log->info('Found conversation #' . $conversation->conversation_id . ' between user #' . $userId . ' and page #' . $pageId);

                // Get ChatMessage From Webhook
                $chat = ChatMessage::getFromWebhook($message, $conversation->conversation_id);

                // Save Message
                $messages->push($this->messages->createOrUpdate($chat->getParams()));
            }
        }

        // Return Messages Collection
        return $messages;
    }
}
