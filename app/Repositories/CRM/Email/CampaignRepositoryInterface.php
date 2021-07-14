<?php

namespace App\Repositories\CRM\Email;

use App\Models\CRM\Email\CampaignSent;
use App\Repositories\Repository;

interface CampaignRepositoryInterface extends Repository {
    /**
     * Mark Campaign as Sent
     * 
     * @param int $campaignId
     * @param int $leadId
     * @param null|string $messageId = null
     * @throws \Exception
     * @return CampaignSent
     */
    public function sent(int $campaignId, int $leadId, ?string $messageId = null): CampaignSent;

    /**
     * Update Sent Campaign
     * 
     * @param int $campaignId
     * @param int $leadId
     * @param string $messageId
     * @throws \Exception
     * @return CampaignSent
     */
    public function updateSent(int $campaignId, int $leadId, string $messageId): CampaignSent;

    /**
     * Replace Sent Message ID
     * 
     * @param string $messageId
     * @param string $newMessageId
     * @return bool
     */
    public function replaceSentMessageId(string $messageId, string $newMessageId): bool;

    /**
     * Was Campaign Already Sent?
     * 
     * @param int $campaignId
     * @param string $email
     * @return bool
     */
    public function wasSent(int $campaignId, string $email): bool;
}
