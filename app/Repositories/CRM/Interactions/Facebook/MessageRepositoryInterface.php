<?php

namespace App\Repositories\CRM\Interactions\Facebook;

use App\Models\CRM\Interactions\Facebook\Message;
use App\Repositories\Repository;

interface MessageRepositoryInterface extends Repository {
    /**
     * Find By ID or Message ID
     * 
     * @param array $params
     * @return null|Message
     */
    public function find(array $params): ?Message;

    /**
     * Create Or Update Message
     * 
     * @param array $params
     * @return Message
     */
    public function createOrUpdate(array $params): Message;
}