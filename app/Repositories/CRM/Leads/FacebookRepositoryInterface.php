<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\Facebook\User;
use App\Models\CRM\Leads\Facebook\Lead as FbLead;
use App\Repositories\Repository;

interface FacebookRepositoryInterface extends Repository {
    /**
     * Find By ID or User ID
     * 
     * @param array $params
     * @return null|User
     */
    public function find(array $params): ?User;

    /**
     * Create Or Update User
     * 
     * @param array $params
     * @return User
     */
    public function createOrUpdate(array $params): User;


    /**
     * Create Facebook Lead
     * 
     * @param int $pageId
     * @param int $userId
     * @param int $leadId
     * @param int $mergeId
     * @return FbLead
     */
    public function convertLead(int $pageId, int $userId, int $leadId, int $mergeId): FbLead;

    /**
     * Lead Exists for Page/User?
     * 
     * @param int $pageId
     * @param int $userId
     * @return bool
     */
    public function leadExists(int $pageId, int $userId): bool;
}