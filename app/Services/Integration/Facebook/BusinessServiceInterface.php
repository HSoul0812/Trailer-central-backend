<?php

namespace App\Services\Integration\Facebook;

use App\Models\Integration\Auth\AccessToken;
use Illuminate\Support\Collection;

interface BusinessServiceInterface {
    /**
     * Set App Type
     * 
     * @param string $type
     * @return void
     */
    public function setAppType(string $type);

    /**
     * Get Page Token
     * 
     * @param AccessToken $accessToken
     * @param int $pageId
     * @return string
     */
    public function pageToken(AccessToken $accessToken, int $pageId): string;

    /**
     * Validate Facebook SDK Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken);

    /**
     * Get Conversations for Page
     * 
     * @param AccessToken $accessToken
     * @param int $pageId
     * @param int $limit default: 0
     * @param string $after default: ''
     * @return Collection<ChatConversation>
     */
    public function getConversations(AccessToken $accessToken, int $pageId, int $limit = 0, string $after = ''): Collection;

    /**
     * Get Conversations for Page
     * 
     * @param AccessToken $accessToken
     * @param string $conversationId
     * @param int $limit default: 0
     * @param string $after default: ''
     * @return Collection<ChatMessage>
     */
    public function getMessages(AccessToken $accessToken, string $conversationId, int $limit = 0, string $after = ''): Collection;
}