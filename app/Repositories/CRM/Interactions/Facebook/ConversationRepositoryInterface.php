<?php

namespace App\Repositories\CRM\Interactions\Facebook;

use App\Models\CRM\Interactions\Facebook\Conversation;
use App\Repositories\Repository;

interface ConversationRepositoryInterface extends Repository {
    /**
     * Find By ID or Conversation ID
     * 
     * @param array $params
     * @return null|Conversation
     */
    public function find(array $params): ?Conversation;

    /**
     * Create Or Update Conversation
     * 
     * @param array $params
     * @return Conversation
     */
    public function createOrUpdate(array $params): Conversation;

    /**
     * Find By Page ID and User ID
     * 
     * @param int $pageId
     * @param int $userId
     * @return null|Conversation
     */
    public function getByParticipants(int $pageId, int $userId): ?Conversation;

    /**
     * Get Newest Conversation Update From Page
     * 
     * @param int $pageId
     * @return null|string
     */
    public function getNewestUpdate(int $pageId): ?string;
}