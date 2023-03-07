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
     * @param int $emailHistoryId
     * @return CampaignSent
     * @throws \Exception
     */
    public function updateSent(int $campaignId, int $leadId, string $messageId, int $emailHistoryId): CampaignSent;

    /**
     * Was Campaign Already Sent?
     *
     * @param int $campaignId
     * @param string $email
     * @return bool
     */
    public function wasSent(int $campaignId, string $email): bool;

    /**
     * Get Campaign Sent Entry for Lead
     *
     * @param int $campaignId
     * @param int $leadId
     * @return null|CampaignSent
     */
    public function getSent(int $campaignId, int $leadId): ?CampaignSent;

    /**
     * Was Campaign Already Sent to Lead?
     *
     * @param int $campaignId
     * @param int $leadId
     * @return bool
     */
    public function wasLeadSent(int $campaignId, int $leadId): bool;

    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
