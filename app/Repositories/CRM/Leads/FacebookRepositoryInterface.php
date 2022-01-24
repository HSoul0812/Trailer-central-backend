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
    public function convertLead(int $pageId, int $userId, int $leadId, int $mergeId = 0): FBLead;

    /**
     * Lead Exists for Page/User?
     *
     * @param int $pageId
     * @param int $userId
     * @return bool
     */
    public function leadExists(int $pageId, int $userId): bool;

    /**
     * Get Facebook Lead
     *
     * @param int $leadId
     * @return null|FbLead
     */
    public function getFbLead(int $leadId): ?FbLead;

    /**
     * @param array $params
     * @return bool
     */
    public function bulkUpdateFbLead(array $params): bool;
}
