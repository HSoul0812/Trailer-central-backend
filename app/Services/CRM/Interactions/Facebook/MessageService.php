<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Models\Integration\Auth\AccessToken;
use App\Models\CRM\Leads\Facebook\User as FbUser;
use App\Repositories\CRM\Interactions\Facebook\ConversationRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\MessageRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Leads\FacebookRepositoryInterface;
use App\Services\CRM\Interactions\Facebook\DTOs\ChatConversation;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\Integration\Facebook\BusinessServiceInterface;
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
        }

        // FB User Assigned to Page?
        if(!$this->users->leadExists($conversation->page_id, $user->user_id)) {
            // Create Facebook Lead
            $lead = $this->leads->create($conversation->getLeadParams());

            // Convert FB User to Lead on Page
            $this->users->convertLead($conversation->page_id, $user->user_id, $lead->identifier);
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
        // Get Conversations
        $conversations = $this->sdk->getConversations($pageToken, $pageId);
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
        }

        // Return Conversations Collection
        return $collection;
    }
}
