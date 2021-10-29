<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Exceptions\CRM\Interactions\Facebook\FacebookLeadDoesntExistException;
use App\Http\Requests\CRM\Interactions\Facebook\SendMessageRequest;
use App\Models\CRM\Leads\Facebook\User as FbUser;
use App\Models\CRM\Interactions\Facebook\Message;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Interactions\Facebook\DTOs\ChatConversation;
use Illuminate\Support\Collection;

interface MessageServiceInterface {
    /**
     * Send Facebook Message
     * 
     * @param SendMessageRequest
     * @throws FacebookLeadDoesntExistException
     * @return Message
     */
    public function send(SendMessageRequest $request): Message;

    /**
     * Create User if Missing
     * 
     * @param ChatConversation $conversation
     * @return FbUser
     */
    public function createUser(ChatConversation $conversation): FbUser;

    /**
     * Scrape Messages From Facebook
     * 
     * @param AccessToken $pageToken
     * @param int $pageId
     * @return Collection<Conversation>
     */
    public function scrapeMessages(AccessToken $pageToken, int $pageId): Collection;
}