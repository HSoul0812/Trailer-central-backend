<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface CampaignRepositoryInterface extends Repository {
    /**
     * Get Leads for Campaign
     * 
     * @param array $params
     * @return Collection
     */
    public function getLeads($params);

    /**
     * Mark Campaign as Sent
     * 
     * @param array $params
     * return CampaignSent
     */
    public function sent($params);
}
