<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use App\Models\CRM\Text\Campaign;
use App\Models\CRM\Text\CampaignSent;
use App\Models\CRM\Text\CampaignBrand;
use App\Models\CRM\Text\CampaignCategory;
use Illuminate\Support\Facades\Log;

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
            Log::error('Text campaign create error. Message - ' . $ex->getMessage() , $ex->getTrace());
            throw new \Exception('Text campaign create error');
        }

        return $campaign;
    }

    public function delete($params) {
        // Get Campaign
        $campaign = Campaign::findOrFail($params['id']);

        // Mark Deleted
        $campaign->fill(['deleted' => '1'])->save();

        // Return
        return $campaign;
    }

    public function get($params) {
        return Campaign::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Campaign::where('deleted', '=', 0)->with('template')
                         ->with('brands')->with('categories');

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

    /**
     * Get All Active Campaigns For Dealer
     *
     * @param int $userId
     * @return Collection of Campaign
     */
    public function getAllActive($userId) {
        return Campaign::where('user_id', $userId)->where('is_enabled', 1)->where('deleted', 0)->get();
    }

    public function update($params) {
        // Find Campaign or Die
        $campaign = Campaign::findOrFail($params['id']);

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

            if (isset($params['log']) && is_array($params['log'])) {
                $log = $campaign->log ? json_decode($campaign->log, true) : [];
                $log[] = $params['log'];
                $params['log'] = json_encode($log);
            }

            // Update Brands
            $this->updateBrands($campaign->id, $brands);

            // Update Categories
            $this->updateCategories($campaign->id, $categories);

            // Fill Text Details
            $campaign->fill($params)->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Text campaign update error. Message - ' . $ex->getMessage() , $ex->getTrace());
            throw new \Exception('Text campaign update error');
        }
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
}
