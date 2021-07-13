<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\CampaignSent;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CampaignRepository implements CampaignRepositoryInterface {

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Campaign::findOrFail($params['id']);
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * Mark Campaign as Sent
     * 
     * @param array $params
     * @throws \Exception
     * @return CampaignSent
     */
    public function sent(array $params): CampaignSent {
        DB::beginTransaction();

        try {
            // Create Campaign Sent
            $sent = CampaignSent::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $sent;
    }

    /**
     * Update Sent Campaign
     * 
     * @param int $campaignId
     * @param int $leadId
     * @param string $messageId
     * @throws \Exception
     * @return CampaignSent
     */
    public function updateSent(int $campaignId, int $leadId, string $messageId): CampaignSent {
        DB::beginTransaction();

        try {
            // Get Campaign Sent Entry
            $sent = CampaignSent::where('drip_campaigns_id', $campaignId)->where('lead_id', $leadId)->first();

            // Update Message ID
            $sent->fill(['message_id' => $messageId]);

            // Save Campaign Sent
            $sent->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $sent;
    }

    /**
     * Was Campaign Already Sent?
     * 
     * @param int $campaignId
     * @param string $email
     * @return bool
     */
    public function wasSent(int $campaignId, string $email): bool {
        // Get Campaign Sent Entry
        $sent = CampaignSent::leftJoin(Lead::getTableName(), Lead::getTableName().'.identifier',  '=', CampaignSent::getTableName().'.lead_id')
                         ->where(CampaignSent::getTableName() . '.drip_campaigns_id', $campaignId)
                         ->where(Lead::getTableName() . '.email_address', $email)->first();

        // Was Campaign Sent?
        return !empty($sent->drip_campaigns_id);
    }
}
