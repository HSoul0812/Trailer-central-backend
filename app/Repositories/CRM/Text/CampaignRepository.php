<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use App\Exceptions\CRM\Text\DuplicateTextCampaignNameException;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Text\Campaign;
use App\Models\CRM\Text\CampaignSent;
use App\Models\CRM\Text\CampaignBrand;
use App\Models\CRM\Text\CampaignCategory;

class CampaignRepository implements CampaignRepositoryInterface {

    private $sortOrders = [
        'name' => [
            'field' => 'campaign_name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'campaign_name',
            'direction' => 'ASC'
        ],
        'subject' => [
            'field' => 'campaign_subject',
            'direction' => 'DESC'
        ],
        '-subject' => [
            'field' => 'campaign_subject',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'modified_at' => [
            'field' => 'modified_at',
            'direction' => 'DESC'
        ],
        '-modified_at' => [
            'field' => 'modified_at',
            'direction' => 'ASC'
        ]
    ];
    
    public function create($params) {
        DB::beginTransaction();

        try {
            // Find Campaign With Name
            $campaignMatch = Campaign::where('campaign_name', $params['campaign_name'])
                                ->where('user_id', $params['user_id'])->first();
            if(!empty($campaignMatch->campaign_name)) {
                throw new DuplicateTextCampaignNameException();
            }

            // Get Categories
            $categories = array();
            if(isset($params['category'])) {
                $categories = $params['category'];
                unset($params['category']);
            }

            // Get Brands
            $brands = array();
            if(isset($params['brand'])) {
                $brands = $params['brand'];
                unset($params['brand']);
            }

            // Create Campaign
            $campaign = Campaign::create($params);

            // Update Brands
            $this->updateBrands($campaign->id, $brands);

            // Update Categories
            $this->updateCategories($campaign->id, $categories);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $campaign;
    }

    public function delete($params) {
        $campaign = Campaign::findOrFail($params['id']);

        DB::transaction(function() use (&$campaign, $params) {
            $params['deleted'] = '1';

            $campaign->fill($params)->save();
        });

        return $campaign;
    }

    public function get($params) {
        return Campaign::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Campaign::where('deleted', '=', 0);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Get Leads for Campaign
     * 
     * @param array $params
     * @return Collection
     */
    public function getLeads($params) {
        // Get Campaign
        $campaign = Campaign::findOrFail($params['id']);
        $crmUser = $campaign->newDealerUser()->first();

        // Find Campaign Leads
        $query = $this->findCampaignLeads($crmUser->id, $campaign);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        // Return Campaign Leads
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        // Find Campaign or Die
        $campaign = Campaign::findOrFail($params['id']);

        DB::transaction(function() use (&$campaign, $params) {
            // Find Campaign With Name
            $campaignMatch = Campaign::where('campaign_name', $params['campaign_name'])
                                ->where('user_id', $params['user_id'])
                                ->where('id', '<>', $params['id'])->first();
            if(!empty($campaignMatch->campaign_name)) {
                throw new DuplicateTextCampaignNameException();
            }

            // Get Categories
            $categories = array();
            if(isset($params['category'])) {
                $categories = $params['category'];
                unset($params['category']);
            }

            // Get Brands
            $brands = array();
            if(isset($params['brand'])) {
                $brands = $params['brand'];
                unset($params['brand']);
            }

            // Update Brands
            $this->updateBrands($campaign->id, $brands);

            // Update Categories
            $this->updateCategories($campaign->id, $categories);

            // Fill Text Details
            $campaign->fill($params)->save();
        });

        return $campaign;
    }

    /**
     * Mark Campaign as Sent
     * 
     * @param array $params
     * return CampaignSent
     */
    public function sent($params) {
        DB::beginTransaction();

        try {
            // Create Campaign Sent
            $stop = CampaignSent::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $stop;
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

    /**
     * Update Campaign Brands
     * 
     * @param int $campaignId
     * @param array $brands
     */
    private function updateBrands($campaignId, $brands) {
        // Delete Old Campaign Brands
        CampaignBrand::deleteByCampaign($campaignId);

        // Create Campaign Brand
        if(count($brands) > 0) {
            foreach($brands as $brand) {
                // Create Brand for Campaign ID
                CampaignBrand::create([
                    'text_campaign_id' => $campaignId,
                    'brand' => $brand
                ]);
            }
        }
    }

    /**
     * Update Campaign Categories
     * 
     * @param int $campaignId
     * @param array $categories
     */
    private function updateCategories($campaignId, $categories) {
        // Delete Old Campaign Categories
        CampaignCategory::deleteByCampaign($campaignId);

        // Create Campaign Category
        if(count($categories) > 0) {
            foreach($categories as $category) {
                // Create Category for Campaign ID
                CampaignCategory::create([
                    'text_campaign_id' => $campaignId,
                    'category' => $category
                ]);
            }
        }
    }

    /**
     * Find Campaign Leads
     * 
     * @param int $dealerId
     * @param Campaign $campaign
     * @return Collection of Leads
     */
    private function findCampaignLeads($dealerId, $campaign)
    {
        // Find Filtered Leads
        $query = Lead::select('website_lead.*')
                     ->leftJoin('inventory', 'website_lead.inventory_id', '=', 'inventory.inventory_id')
                     ->leftJoin('crm_tc_lead_status', 'website_lead.identifier', '=', 'crm_tc_lead_status.tc_lead_identifier')
                     ->leftJoin('crm_text_stop', 'website_lead.phone_number', '=', 'crm_text_stop.sms_number')
                     ->where('website_lead.dealer_id', $dealerId)
                     ->where('website_lead.phone_number', '<>', '')
                     ->whereNotNull('website_lead.phone_number')
                     ->whereNull('crm_text_stop.sms_number');

        // Is Archived?!
        if($campaign->included_archived === -1 || $campaign->include_archived === '-1') {
            $query = $query->where('website_lead.is_archived', 0);
        } elseif($campaign->included_archived !== 0 && $campaign->include_archived === '0') {
            $query = $query->where('website_lead.is_archived', $campaign->include_archived);
        }

        // Get Categories
        if(!empty($campaign->categories)) {
            $categories = array();
            foreach($campaign->categories as $category) {
                $categories[] = $category->category;
            }

            // Add IN
            if(count($categories) > 0) {
                $query = $query->whereIn('inventory.category', $categories);
            }
        }

        // Get Brands
        if(!empty($campaign->brands)) {
            $brands = array();
            foreach($campaign->brands as $brand) {
                $brands[] = $brand->brand;
            }

            // Add IN
            if(count($brands) > 0) {
                $query = $query->whereIn('inventory.manufacturer', $brands);
            }
        }
        
        // Toggle Action
        if($campaign->action === 'purchased') {
            $query = $query->where(function (Builder $query) {
                $query->where('crm_tc_lead_status.status', Lead::STATUS_WON)
                      ->orWhere('crm_tc_lead_status.status', Lead::STATUS_WON_CLOSED);
            });
        } else {
            $query = $query->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON)
                           ->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON_CLOSED);
        }

        // Return Filtered Query
        return $query->where(function (Builder $query) use($campaign) {
            return $query->where('website_lead.dealer_location_id', $campaign->location_id)
                         ->orWhereRaw('(website_lead.dealer_location_id = 0 AND inventory.dealer_location_id = ?)', [$campaign->location_id]);
        })->whereRaw('DATE_ADD(website_lead.date_submitted, INTERVAL +' . $campaign->send_after_days . ' DAY) < NOW()')
          ->whereRaw('(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(website_lead.date_submitted)) / (60 * 60 * 24)) - ' . $campaign->send_after_days . ') <= 10');
    }
}
