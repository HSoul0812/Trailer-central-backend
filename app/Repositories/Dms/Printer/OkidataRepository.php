<?php

namespace App\Repositories\Dms\Printer;

use App\Repositories\Dms\Printer\OkidataRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Printer\Okidata;

class OkidataRepository implements OkidataRepositoryInterface {

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * Get Okidata Form
     * 
     * @param array{id: int} $params
     * @throws \Exception when Okidata form does not exist
     * @return Okidata
     */
    public function get($params) {
        return Okidata::findOrFail($params['id']);
    }

    /**
     * Get All Okidata Forms With Filters
     * 
     * @param array $params
     * @return Collection<Okidata>
     */
    public function getAll($params) {
        $query = Okidata::where('id', '>', 0);

        // Search Term
        if(isset($params['search_term'])) {
            $query->leftJoin(Region::getTableName(), Okidata::getTableName().'.region', '=', Region::getTableName().'.region_code')
                  ->where(function($query) use($params) {
                $query->where('region', $params['search_term'])
                      ->orWhere('name', 'LIKE', '%' . $params['search_term'] . '%')
                      ->orWhere('description', 'LIKE', '%' . $params['search_term'] . '%')
                      ->orWhere('department', 'LIKE', '%' . $params['search_term'] . '%')
                      ->orWhere('division', 'LIKE', '%' . $params['search_term'] . '%')
                      ->orWhere(Region::getTableName() . '.region_name', 'LIKE', '%' . $params['search_term'] . '%');
            });
        }

        // Find By Name
        if(isset($params['name'])) {
            $query->where('name', $params['name']);
        }

        // Find By Region
        if(isset($params['region'])) {
            $query->where('region', $params['region']);
        }

        // Find By Department
        if(isset($params['department'])) {
            $query->where('department', $params['department']);
        }

        // Find By Division
        if(isset($params['division'])) {
            $query->where('division', $params['division']);
        }

        // Return Collection
        return $query->get();
    }

    public function update($params) {
        throw new NotImplementedException;
    }
}