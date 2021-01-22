<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadImport;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use Illuminate\Support\Collection;

class ImportRepository implements ImportRepositoryInterface 
{
    public function create($params) {
        return LeadImport::create($params);
    }

    public function delete($params) {
        // Get Lead Import
        return LeadImport::findOrFail($params['id'])->delete();
    }

    /**
     * Delete All For Params (dealer_id required)
     * 
     * @param array $params
     * @return bool
     */
    public function deleteAll($params) {
        // Delete All for Dealer ID
        return LeadImport::where('dealer_id', $params['dealer_id'])->delete();
    }

    public function get($params) {
        return LeadImport::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = LeadImport::where('id', '>', 0);

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('dealer_location_id', $params['dealer_location_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $leadImport = LeadImport::findOrFail($params['id']);

        DB::transaction(function() use (&$leadImport, $params) {
            // Fill Lead Import Details
            $leadImport->fill($params)->save();
        });

        return $leadImport;
    }


    /**
     * Get All Active Lead Import Emails
     * 
     * @return Collection<LeadImport>
     */
    public function getAllActive() : Collection
    {
        // Initialize Lead Imports
        return LeadImport::select(LeadImport::getTableName() . '.*')
                          ->leftJoin(NewDealerUser::getTableName(),
                                     NewDealerUser::getTableName() . '.id', '=',
                                     LeadImport::getTableName() . '.dealer_id')
                          ->leftJoin(CrmUser::getTableName(),
                                     CrmUser::getTableName() . '.user_id', '=',
                                     NewDealerUser::getTableName() . '.user_id')
                          ->where(CrmUser::getTableName() . '.active', 1)->get();
    }

    /**
     * Find Import Entry in Lead Import Table?
     * 
     * @param array $params
     * @return LeadImport || null
     */
    public function find($params)
    {
        // Find Lead Import By ID or Email
        if(isset($params['id'])) {
            return LeadImport::find($params['id']);
        }

        // Email Set?
        if(isset($params['email']) && empty($import->id)) {
            return LeadImport::where('email', $params['email'])->first();
        }

        // Return Result
        return null;
    }
}
