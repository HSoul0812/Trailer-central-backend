<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Models\CRM\Leads\Facebook\User as FbUser;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Interactions\Facebook\DTOs\ChatConversation;
use Illuminate\Support\Collection;

interface MessageServiceInterface {
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