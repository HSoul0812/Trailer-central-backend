<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Exceptions\CRM\Interactions\Facebook\FacebookLeadDoesntExistException;
use App\Http\Requests\CRM\Interactions\Facebook\SendMessageRequest;
use App\Models\Integration\Auth\AccessToken;
use App\Models\CRM\Leads\Facebook\User as FbUser;
use App\Models\CRM\Interactions\Facebook\Message;
use App\Repositories\CRM\Interactions\Facebook\ConversationRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\MessageRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Leads\FacebookRepositoryInterface;
use App\Services\CRM\Interactions\Facebook\DTOs\ChatConversation;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\Integration\Facebook\BusinessService;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Class MessageService
 * 
 * @package App\Services\CRM\Interactions\Facebook
 */
class MessageService implements MessageServiceInterface
{
    /**
     * @var ConversationRepositoryInterface $conversations
     */
    protected $conversations;

    /**
     * @var MessageRepositoryInterface $messages
     */
    protected $messages;

    /**
     * @var FacebookRepositoryInterface $users
     */
    protected $users;

    /**
     * @var InteractionsRepositoryInterface $interactions
     */
    protected $interactions;

    /**
     * @var LeadServiceInterface $leads
     */
    protected $leads;

    /**
     * @var BusinessServiceInterface $sdk
     */
    protected $sdk;


    /**
     * InteractionsRepository constructor.
     * 
     * @param InteractionsRepositoryInterface $interactions
     */
    public function __construct(
        ConversationRepositoryInterface $conversations,
        MessageRepositoryInterface $messages,
        FacebookRepositoryInterface $users,
        InteractionsRepositoryInterface $interactions,
        LeadServiceInterface $leads,
        BusinessServiceInterface $sdk
    ) {
        $this->conversations = $conversations;
        $this->messages = $messages;
        $this->users = $users;
        $this->interactions = $interactions;
        $this->leads = $leads;
        $this->sdk = $sdk;
        $this->sdk->setAppType(BusinessService::APP_TYPE_CHAT);

        // Initialize Logger
        $this->log = Log::channel('facebook');
    }

    /**
     * Send Facebook Message
     * 
     * @param SendMessageRequest
     * @throws FacebookLeadDoesntExistException
     * @return Message
     */
    public function send(SendMessageRequest $request): Message {
        // Get Facebook Lead
        $fbLead = $this->users->getFbLead($request->lead_id);
        if(empty($fbLead)) {
            throw new FacebookLeadDoesntExistException;
        }

        // Send Message
        $messageId = $this->sdk->sendMessage($fbLead->page->accessToken, $fbLead->conversation->user_id, $request->message, $request->type);

        // Save Message to DB
        return $this->messages->createOrUpdate([
            'message_id' => $messageId,
            'conversation_id' => $fbLead->conversation->conversation_id,
            'user_id' => $fbLead->conversation->user_id,
            'from_id' => $fbLead->conversation->page_id,
            'to_id' => $fbLead->conversation->user_id,
            'message' => $request->message
        ]);
    }

    /**
     * Create User if Missing
     * 
     * @param ChatConversation $conversation
     * @return FbUser
     */
    public function createUser(ChatConversation $conversation): FbUser {
        // Check if User Already Created
        $user = $this->users->find($conversation->user->getParams());

        // No User?
        if(empty($user->user_id)) {
            $user = $this->users->create($conversation->user->getParams());
            $this->log->debug('Created new facebook user with ID #' . $user->user_id);
        } else {
            $this->log->debug('Found existing facebook user with ID #' . $user->user_id);
        }

        // FB User Assigned to Page?
        if(!$this->users->leadExists($conversation->pageId, $user->user_id)) {
            // Lead Does Not Exist?
            $this->log->debug('Lead doesn\'t exist for page #' . $conversation->pageId . ' and user #' . $user->user_id);

            // Create Facebook Lead
            $lead = $this->leads->create($conversation->getLeadParams());

            // Convert FB User to Lead on Page
            $this->users->convertLead($conversation->pageId, $user->user_id, $lead->identifier);
            $this->log->debug('Created lead #' . $lead->identifier . ' for page #' . $conversation->pageId . ' and user #' . $user->user_id);
        } else {
            $this->log->debug('Lead already exists for page #' . $conversation->pageId . ' and user #' . $user->user_id);
        }

        // Return Facebook User
        return $user;
    }

    /**
     * Scrape Messages From Facebook
     * 
     * @param AccessToken $pageToken
     * @param int $pageId
     * @return Collection<Conversation>
     */
    public function scrapeMessages(AccessToken $pageToken, int $pageId): Collection {
        // Get Newest Conversation Update
        $newestUpdate = $this->conversations->getNewestUpdate($pageId);

        // Get Conversations
        $conversations = $this->sdk->filterConversations($pageToken, $pageId, $newestUpdate);
        $this->log->debug('Retrieved ' . $conversations->count() . ' conversations from Page #' . $pageId);

        // Loop Conversations
        $collection = new Collection();
        foreach($conversations as $conversation) {
            // Create User
            $this->createUser($conversation);

            // Create Conversation
            $convo = $this->conversations->createOrUpdate($conversation->getParams());
            $collection->push($convo);

            // Get Messages
            $messages = $this->sdk->getMessages($pageToken, $conversation->conversationId);
            foreach($messages as $message) {
                $this->messages->createOrUpdate($message->getParams());
            }
            $this->log->debug('Updated ' . $messages->count() . ' messages for conversation #' . $conversation->conversationId);
        }
        $this->log->debug('Updated ' . $conversations->count() . ' conversations from Page #' . $pageId);

        // Return Conversations Collection
        return $collection;
    }
}
