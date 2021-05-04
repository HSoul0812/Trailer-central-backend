<?php

namespace App\Repositories\CRM\Email;

use App\Repositories\Repository;

interface CampaignRepositoryInterface extends Repository {
    /**
     * Mark Campaign as Sent
     * 
     * @param array $params
     * return CampaignSent
     */
    public function sent($params);
}
