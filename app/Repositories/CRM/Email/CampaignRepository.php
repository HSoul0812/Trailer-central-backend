<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\CampaignSent;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CampaignRepository implements CampaignRepositoryInterface {

    private $sortOrders = [
        'name' => [
            'field' => 'campaign_name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'campaign_name',
            'direction' => 'ASC'
        ]
    ];

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
        
        $query = Campaign::with('template');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 20;
        }

        if (isset($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }

        if (isset($params['is_enabled'])) {
            $query = $query->where('is_enabled', !empty($params['is_enabled']) ? 1 : 0);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * Mark Campaign as Sent
     *
     * @param int $campaignId
     * @param int $leadId
     * @param null|string $messageId = null
     * @throws \Exception
     * @return CampaignSent
     */
    public function sent(int $campaignId, int $leadId, ?string $messageId = null): CampaignSent {
        // Get Sent?
        $sent = $this->getSent($campaignId, $leadId);
        if(!empty($sent->drip_campaigns_id)) {
            return $sent;
        }

        DB::beginTransaction();

        try {
            // Create Campaign Sent
            $sent = CampaignSent::create([
                'drip_campaigns_id' => $campaignId,
                'lead_id' => $leadId,
                'message_id' => $messageId ?? ''
            ]);

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
     * @param string|null $messageId
     * @param int $emailHistoryId
     * @return CampaignSent
     * @throws \Exception
     */
    public function updateSent(int $campaignId, int $leadId, ?string $messageId, int $emailHistoryId): CampaignSent {
        // Get Campaign Sent Entry
        $sent = CampaignSent::where('drip_campaigns_id', $campaignId)->where('lead_id', $leadId)->first();
        if(empty($sent->drip_campaigns_id)) {
            return $this->sent($campaignId, $leadId, $messageId);
        }

        $params = ['crm_email_history_id' => $emailHistoryId];

        if ($messageId) {
            $params['message_id'] = $messageId;
        }

        // Update Message ID
        $sent->fill($params);

        // Save Campaign Sent
        $sent->save();

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
        $sent = CampaignSent::select(CampaignSent::getTableName().'.*')
                            ->leftJoin(Lead::getTableName(), Lead::getTableName().'.identifier',  '=', CampaignSent::getTableName().'.lead_id')
                            ->where(CampaignSent::getTableName() . '.drip_campaigns_id', $campaignId)
                            ->where(Lead::getTableName() . '.email_address', $email)->first();

        // Was Campaign Sent?
        return !empty($sent->drip_campaigns_id);
    }

    /**
     * Get Campaign Sent Entry for Lead
     *
     * @param int $campaignId
     * @param int $leadId
     * @return null|CampaignSent
     */
    public function getSent(int $campaignId, int $leadId): ?CampaignSent {
        // Get Campaign Sent Entry
        return CampaignSent::where('drip_campaigns_id', $campaignId)->where('lead_id', $leadId)->first();
    }

    /**
     * Was Campaign Already Sent to Lead?
     *
     * @param int $campaignId
     * @param int $leadId
     * @return bool
     */
    public function wasLeadSent(int $campaignId, int $leadId): bool {
        // Get Campaign Sent Entry
        $sent = $this->getSent($campaignId, $leadId);

        // Successful?
        return !empty($sent->drip_campaigns_id);
    }

    /**
     * Add Sort Query
     *
     * @param type $query
     * @param type $sort
     * @return type
     */
    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
