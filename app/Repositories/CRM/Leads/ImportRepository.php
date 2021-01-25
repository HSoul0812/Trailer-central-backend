<?php

namespace App\Repositories\CRM\Leads;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\LeadImport;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportRepository implements ImportRepositoryInterface 
{
    /**
     * Create Import for Dealer
     * 
     * @param array $params
     * @return LeadImport
     */
    public function create($params): LeadImport {
        return LeadImport::create($params);
    }

    /**
     * Delete All Imports for Dealer
     * 
     * @param array $params
     * @return bool
     */
    public function delete($params) {
        // Get Lead Import
        $query = LeadImport::where('dealer_id', $params['dealer_id']);

        // Email Set?
        if(isset($params['email'])) {
            $query->where('email', $params['email']);
        }

        // Return Result
        return $query->delete();
    }

    /**
     * Get Single Lead Import
     * 
     * @param type $params
     * @throws NotImplementedException
     */
    public function get($params) {
        throw new NotImplementedException;
    }

    /**
     * Get All Lead Imports By Dealer
     * 
     * @param array $params
     * @return pagination
     */
    public function getAll($params) {
        // Get All By Dealer ID
        $query = LeadImport::where('dealer_id', $params['dealer_id']);

        if(isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->get();
    }

    /**
     * Update All Imports By Dealer
     * 
     * @param array $params
     * @return Collection<LeadImport>
     */
    public function update($params): Collection {
        // Get All Imports
        $imports = $this->getAll($params);

        // Start Transaction
        DB::transaction(function() use (&$imports, $params) {
            // Delete Existing
            $this->delete($params);

            // Empty Collection
            $imports = new Collection();

            // Create New For Dealer
            foreach($params['emails'] as $email) {
                // Create Import
                $import = $this->create([
                    'dealer_id' => $params['dealer_id'],
                    'email' => $email
                ]);
                $imports->push($import);
            }
        });

        // Collect Imports
        return $imports;
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
        // Find Lead Imports By Email and Dealer Name
        return LeadImport::select(LeadImport::getTableName() . '.*')
                         ->leftJoin(User::getTableName(),
                                    User::getTableName() . '.dealer_id', '=',
                                    LeadImport::getTableName() . '.dealer_id')
                         ->where(LeadImport::getTableName() . '.email', $params['email'])
                         ->where(User::getTableName() . '.name', $params['name']);
    }
}
