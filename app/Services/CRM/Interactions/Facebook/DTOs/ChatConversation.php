<?php

namespace App\Services\CRM\Interactions\Facebook\DTOs;

use App\Models\CRM\Leads\LeadType;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use FacebookAds\Object\UnifiedThread;

/**
 * Class ChatConversation
 * 
 * @package App\Services\CRM\Interactions\Facebook\DTOs
 */
class ChatConversation
{
    use WithConstructor, WithGetter;

    /**
     * @var string Conversation ID
     */
    private $conversationId;

    /**
     * @var int Page ID
     */
    private $pageId;

    /**
     * @var Page DB Object of Page
     */
    private $page;

    /**
     * @var FacebookUser DTO of Facebook User
     */
    private $user;

    /**
     * @var string Link to Conversation
     */
    private $link;

    /**
     * @var string Snippet of Conversation
     */
    private $snippet;

    /**
     * @var string Newest Update
     */
    private $newestUpdate;


    /**
     * Get From UnifiedThread
     * 
     * UnifiedThread $conversation
     * @return ChatMessage
     */
    public static function getFromUnifiedThread(UnifiedThread $conversation, Page $page): ChatConversation {
        // Create ChatConversation
        return new self([
            'conversation_id' => $conversation->id,
            'page_id' => $page->page_id,
            'page' => $page,
            'user' => $this->parseUser($conversation->participants->data, $page->page_id),
            'link' => $conversation->link,
            'snippet' => $conversation->snippet,
            'newest_update' => Carbon::parse($conversation->newest_update)->toDateTimeString(),
        ]);
    }

    /**
     * Parse Facebook User From Participants
     * 
     * @return FacebookUser
     */
    public static function parseUser(array $users, int $pageId): FacebookUser {
        // Loop Participants
        $chosen = null;
        foreach($users as $user) {
            if((int) $user->id !== $pageId) {
                $chosen = $user;
            }
        }

        // Return Result
        return new FacebookUser([
            'user_id' => $chosen->id,
            'name' => $chosen->name,
            'email' => $chosen->email
        ]);
    }

    /**
     * Get Params For Conversation
     * 
     * @return array{conversation_id: string,
     *               page_id: int,
     *               user_id: int,
     *               link: string,
     *               snippet: string,
     *               newest_update: string}
     */
    public function getParams(): array {
        return [
            'conversation_id' => $this->conversationId,
            'page_id' => $this->pageId,
            'user_id' => $this->user->userId,
            'link' => $this->link,
            'snippet' => $this->snippet,
            'newest_update' => $this->newestUpdate
        ];
    }

    /**
     * Get Params For Facebook Lead
     * 
     * @return array{website_id: int,
     *               dealer_id: int,
     *               lead_type: string,
     *               first_name: string,
     *               last_name: string,
     *               email_address: string,
     *               referral: string,
     *               comments: string,
     *               newest_update: string}
     */
    public function getLeadParams(): array {
        return [
            'website_id' => $this->page->website->website_id,
            'dealer_id' => $this->page->dealer_id,
            'lead_type' => LeadType::TYPE_NONLEAD,
            'first_name' => $this->user->getFirstName(),
            'last_name' => $this->user->getLastName(),
            'email_address' => $this->user->email,
            'preferred_contact' => 'email',
            'referral' => $this->link,
            'comments' => $this->snippet,
            'date_submitted' => $this->newestUpdate,
            'lead_source' => 'Facebook Messenger'
        ];
    }
}