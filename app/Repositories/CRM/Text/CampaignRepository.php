<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Text\Campaign;
use App\Models\CRM\Text\CampaignSent;

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
            // Get Brands/Categories
            var_dump($params);
            die;
            $categories = $params['category'];
            $brands = $params['brand'];
            unset($params['category']);
            unset($params['brand']);

            // Create Campaign
            $campaign = Campaign::create($params);

            // Update Blasts
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

    public function update($params) {
        // Find Campaign or Die
        $campaign = Campaign::findOrFail($params['id']);

        DB::transaction(function() use (&$campaign, $params) {
            // Get Brands/Categories
            $categories = $params['category'];
            $brands = $params['brand'];
            unset($params['category']);
            unset($params['brand']);

            // Update Blasts
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
     * Update Blast Brands
     * 
     * @param int $campaignId
     * @param array $brands
     */
    private function updateBrands($campaignId, $brands) {
        // Delete Old Blast Brands
        BlastBrand::findByBlast($campaignId)->delete();

        // Create Blast Brand
        if(count($brands) > 0) {
            foreach($brands as $brand) {
                // Create Brand for Blast ID
                BlastBrand::create([
                    'text_blast_id' => $campaignId,
                    'brand' => $brand
                ]);
            }
        }
    }

    /**
     * Update Blast Categories
     * 
     * @param int $campaignId
     * @param array $categories
     */
    private function updateCategories($campaignId, $categories) {
        // Delete Old Blast Categories
        BlastCategory::findByBlast($campaignId)->delete();

        // Create Blast Category
        if(count($categories) > 0) {
            foreach($categories as $category) {
                // Create Category for Blast ID
                BlastCategory::create([
                    'text_blast_id' => $campaignId,
                    'category' => $category
                ]);
            }
        }
    }
}
