<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\ConversationRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\MessageRepositoryInterface;
use App\Services\CRM\Interactions\Facebook\ChatMessage;
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
            $pageId = $entry->id;

            // Process Messages for Page
            foreach($entry->messaging as $message) {
                // Get User ID
                $userId = $message->sender->id === $pageId ? $message->recipient->id : $message->sender->id;

                // Find Conversation ID
                $conversation = $this->conversations->getByParticipants($pageId, $userId);
                $this->log->info('Found conversation #' . $conversation->conversation_id . ' between user #' . $userId . ' and page #' . $pageId);

                // Get ChatMessage From Webhook
                $chat = ChatMessage::getFromWebhook($message, $pageId, $conversation->conversation_id);

                // Save Message
                $messages->push($this->messages->create($chat->getParams()));
            }
        }

        // Return Messages Collection
        return $messages;
    }
}
