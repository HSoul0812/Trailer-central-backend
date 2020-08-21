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

    /**
     * Get Leads for Blast
     * 
     * @param array $params
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

        // Return All?
        if($params['per_page'] === 'all') {
            return $query->get();
        }

        // Return Blast Leads
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $blast = Blast::findOrFail($params['id']);

        DB::transaction(function() use (&$blast, $params) {
            // Find Blast With Name
            $blastMatch = Blast::where('campaign_name', $params['campaign_name'])
                                ->where('user_id', $params['user_id'])
                                ->where('id', '<>', $params['id'])->first();
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
                     ->leftJoin('crm_text_blast_sent', function($join) use($campaign) {
                        return $join->on('crm_text_blast_sent.lead_id', '=', 'website_lead.identifier')
                                    ->where('crm_text_blast_sent.text_blast_id', '=', $blast->id);
                     })
                     ->leftJoin('crm_tc_lead_status', 'website_lead.identifier', '=', 'crm_tc_lead_status.tc_lead_identifier')
                     ->leftJoin('crm_text_stop', function($join) {
                        return $join->on(DB::raw("CONCAT('+1', SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(website_lead.phone_number, '(', ''), ')', ''), '-', ''), ' ', ''), '-', ''), '+', ''), '.', ''), 1, 10))"), '=', 'crm_text_stop.sms_number')
                                    ->orOn(DB::raw("CONCAT('+', SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(website_lead.phone_number, '(', ''), ')', ''), '-', ''), ' ', ''), '-', ''), '+', ''), '.', ''), 1, 11))"), '=', 'crm_text_stop.sms_number');
                     })
                     ->where('website_lead.dealer_id', $dealerId)
                     ->where('website_lead.phone_number', '<>', '')
                     ->whereNotNull('website_lead.phone_number')
                     ->whereNull('crm_text_stop.sms_number')
                     ->whereNull('crm_text_blast_sent.text_blast_id');

        // Is Archived?!
        if($blast->included_archived === -1 || $blast->include_archived === '-1') {
            $query = $query->where('website_lead.is_archived', 0);
        } elseif($blast->included_archived !== 0 && $blast->include_archived === '0') {
            $query = $query->where('website_lead.is_archived', $blast->include_archived);
        }

        // Get Categories
        if(!empty($blast->categories)) {
            $categories = array();
            foreach($blast->categories as $category) {
                $categories[] = $category->category;
            }

            // Add IN
            if(count($categories) > 0) {
                $query = $query->whereIn('inventory.category', $categories);
            }
        }

        // Get Brands
        if(!empty($blast->brands)) {
            $brands = array();
            foreach($blast->brands as $brand) {
                $brands[] = $brand->brand;
            }

            // Add IN
            if(count($brands) > 0) {
                $query = $query->whereIn('inventory.manufacturer', $brands);
            }
        }
        
        // Toggle Action
        if($blast->action === 'purchased') {
            $query = $query->where(function (Builder $query) {
                $query->where('crm_tc_lead_status.status', Lead::STATUS_WON)
                      ->orWhere('crm_tc_lead_status.status', Lead::STATUS_WON_CLOSED);
            });
        } else {
            $query = $query->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON)
                           ->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON_CLOSED);
        }

        // Return Filtered Query
        return $query->where(function (Builder $query) use($blast) {
            return $query->where('website_lead.dealer_location_id', $blast->location_id)
                         ->orWhereRaw('(website_lead.dealer_location_id = 0 AND inventory.dealer_location_id = ?)', [$blast->location_id]);
        })->whereRaw('DATE_ADD(website_lead.date_submitted, INTERVAL +' . $blast->send_after_days . ' DAY) >= NOW()');
    }

}
