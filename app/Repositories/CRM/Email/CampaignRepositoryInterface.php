<?php

namespace App\Repositories\CRM\Email;

use App\Models\CRM\Text\CampaignSent;
use App\Repositories\Repository;

interface CampaignRepositoryInterface extends Repository {
    /**
     * Mark Campaign as Sent
     * 
     * @param array $params
     * @throws \Exception
     * @return CampaignSent
     */
    public function sent(array $params): CampaignSent;

    /**
     * Was Campaign Already Sent?
     * 
     * @param int $campaignId
     * @param int $leadId
     * @return bool
     */
    public function wasSent(int $campaignId, int $leadId): bool;
}
