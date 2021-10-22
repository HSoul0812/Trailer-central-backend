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
     * Filter Conversations for Page
     * 
     * @param AccessToken $accessToken
     * @param int $pageId
     * @param null|string $time
     * @throws ExpiredFacebookAccessTokenException
     * @throws FailedGetConversationsException
     * @return Collection<ChatConversation>
     */
    public function filterConversations(AccessToken $accessToken, int $pageId, ?string $time = null): Collection;

    /**
     * Get Conversations for Page
     * 
     * @param int $pageId
     * @param string $time
     * @param Collection $collection
     * @param string $after default: ''
     * @param int $limit default: 0
     * @return Collection<ChatConversation>
     */
    public function getConversations(int $pageId, string $time, Collection $collection, string $after = '', int $limit = 0): Collection;

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