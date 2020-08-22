<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Exceptions\CRM\Text\DuplicateTextBlastNameException;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Text\Blast;
use App\Models\CRM\Text\BlastSent;
use App\Models\CRM\Text\BlastBrand;
use App\Models\CRM\Text\BlastCategory;
use Carbon\Carbon;

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
            // Find Blast With Name
            $blastMatch = Blast::where('campaign_name', $params['campaign_name'])
                                ->where('user_id', $params['user_id'])->first();
            if(!empty($blastMatch->campaign_name)) {
                throw new DuplicateTextBlastNameException();
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

        if (isset($params['is_delivered'])) {
            $query = $query->where('is_delivered', !empty($params['is_delivered']) ? 1 : 0);
        }

        if (isset($params['is_cancelled'])) {
            $query = $query->where('is_cancelled', !empty($params['is_cancelled']) ? 1 : 0);
        }

        if (isset($params['send_date'])) {
            if($params['send_date'] === 'due_now') {
                $query = $query->where('send_date', '<', Carbon::now()->toDateTimeString());
            } else {
                $query = $query->where('send_date', '<', $params['send_date']);
            }
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        // Return All?
        if($params['per_page'] === 'all') {
            return $query->get();
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $blast = Blast::findOrFail($params['id']);

        DB::transaction(function() use (&$blast, $params) {
            // Find Blast With Name
            if(isset($params['campaign_name'])) {
                $blastMatch = Blast::where('campaign_name', $params['campaign_name'])
                                    ->where('user_id', $blast->user_id)
                                    ->where('id', '<>', $params['id'])->first();
                if(!empty($blastMatch->campaign_name)) {
                    throw new DuplicateTextBlastNameException();
                }
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

            // Update Blasts
            $this->updateBrands($blast->id, $brands);

            // Update Categories
            $this->updateCategories($blast->id, $categories);

            // Fill Text Details
            $blast->fill($params)->save();
        });

        return $blast;
    }

    /**
     * Mark Blast as Sent
     * 
     * @param array $params
     * return BlastSent
     */
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
}
