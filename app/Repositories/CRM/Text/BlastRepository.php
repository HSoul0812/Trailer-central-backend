<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Text\Blast;
use App\Models\CRM\Text\BlastSent;
use App\Models\CRM\Text\BlastBrand;
use App\Models\CRM\Text\BlastCategory;

class BlastRepository implements BlastRepositoryInterface {

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

            // Create Blast
            $blast = Blast::create($params);

            // Update Blasts
            $this->updateBrands($blast->id, $brands);

            // Update Categories
            $this->updateCategories($blast->id, $categories);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $blast;
    }

    public function delete($params) {
        $blast = Blast::findOrFail($params['id']);

        DB::transaction(function() use (&$blast, $params) {
            $params['deleted'] = '1';

            $blast->fill($params)->save();
        });

        return $blast;
    }

    public function get($params) {
        return Blast::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Blast::where('deleted', '=', 0);
        
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
     * Get Leads for Blast
     * 
     * @param int $id
     * @return Collection
     */
    public function getLeads($params) {
        // Get Blast
        $blast = Blast::findOrFail($params['id']);
        $crmUser = $blast->newDealerUser()->first();

        // Find Blast Leads
        $query = $this->findBlastLeads($crmUser->id, $blast);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        // Return Blast Leads
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $blast = Blast::findOrFail($params['id']);

        DB::transaction(function() use (&$blast, $params) {
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

            // Update Blasts
            $this->updateBrands($blast->id, $brands);

            // Update Categories
            $this->updateCategories($blast->id, $categories);

            // Fill Text Details
            $blast->fill($params)->save();
        });

        return $blast;
    }

    public function sent($params) {
        DB::beginTransaction();

        try {
            // Create Blast Sent
            $stop = BlastSent::create($params);

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
     * @param int $blastId
     * @param array $brands
     */
    private function updateBrands($blastId, $brands) {
        // Delete Old Blast Brands
        BlastBrand::deleteByBlast($blastId);

        // Create Blast Brand
        if(count($brands) > 0) {
            foreach($brands as $brand) {
                // Create Brand for Blast ID
                BlastBrand::create([
                    'text_blast_id' => $blastId,
                    'brand' => $brand
                ]);
            }
        }
    }

    /**
     * Update Blast Categories
     * 
     * @param int $blastId
     * @param array $categories
     */
    private function updateCategories($blastId, $categories) {
        // Delete Old Blast Categories
        BlastCategory::deleteByBlast($blastId);

        // Create Blast Category
        if(count($categories) > 0) {
            foreach($categories as $category) {
                // Create Category for Blast ID
                BlastCategory::create([
                    'text_blast_id' => $blastId,
                    'category' => $category
                ]);
            }
        }
    }

    /**
     * Find Blast Leads
     * 
     * @param int $dealerId
     * @param Blast $blast
     * @return Collection of Leads
     */
    private function findBlastLeads($dealerId, $blast)
    {
        // Find Filtered Leads
        $query = Lead::select('website_lead.*')
                     ->leftJoin('inventory', 'website_lead.inventory_id', '=', 'inventory.inventory_id')
                     ->where('dealer_id', $dealerId);

        // Is Archived?!
        if($blast->included_archived !== -1) {
            $query = $query->where('website_lead.is_archived', $blast->include_archived);
        }

        // Get Categories
        if(!empty($blast->categories)) {
            $categories = array();
            foreach($blast->categories as $category) {
                $categories[] = $category->category;
            }

            // Add IN
            $query = $query->whereIn('inventory.category', $categories);
        }

        // Get Categories
        if(!empty($blast->categories)) {
            $categories = array();
            foreach($blast->categories as $category) {
                $categories[] = $category->category;
            }

            // Add IN
            $query = $query->whereIn('inventory.category', $categories);
        }

        // Get Brands
        if(!empty($blast->brands)) {
            $brands = array();
            foreach($blast->brands as $brand) {
                $brands[] = $brand->brand;
            }

            // Add IN
            $query = $query->whereIn('inventory.manufacturer', $brands);
        }

        // Return Filtered Query
        return $query->where(function (Builder $query) use($blast) {
            return $query->where('website_lead.dealer_location_id', $blast->location_id)
                    ->orWhereRaw('(website_lead.dealer_location_id = 0 AND inventory.dealer_location_id = ?)', [$blast->location_id]);
        })->whereRaw('DATE_ADD(website_lead.date_submitted, INTERVAL +' . $blast->send_after_days . ' DAY) > NOW()');
    }

}
