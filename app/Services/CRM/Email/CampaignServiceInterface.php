<?php

namespace App\Services\CRM\Email;

use App\Models\CRM\Email\Campaign;

/**
 * Interface CampaignServiceInterface
 * @package App\Services\CRM\Email
 */
interface CampaignServiceInterface
{
    /**
     * @param array $params
     * @return Campaign
     */
    public function create(array $params): Campaign;

    /**
     * @param array $params
     * @return Campaign
     */
    public function update(array $params): Campaign;

    /**
     * @param array $params
     * @return bool
     */
    public function delete(array $params): ?bool;
}
