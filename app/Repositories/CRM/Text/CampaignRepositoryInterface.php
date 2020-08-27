<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface CampaignRepositoryInterface extends Repository {
    /**
     * Get All Active Campaigns For Dealer
     * 
     * @param int $userId
     * @return Collection of Campaign
     */
    public function getAllActive($userId);

    /**
     * Mark Campaign as Sent
     * 
     * @param array $params
     * return CampaignSent
     */
    public function sent($params);
}
