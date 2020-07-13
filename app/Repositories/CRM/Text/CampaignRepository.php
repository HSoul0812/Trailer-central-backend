<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
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
     * @param int $id
     * @return Collection
     */
    public function getLeads($params) {
        // Get Campaign
        $campaign = Campaign::findOrFail($params['id']);
        $crmUser = $campaign->crmUser()->first();

        // Find Campaign Leads
        $query = Lead::findLeads($params['id']);
        
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
}
