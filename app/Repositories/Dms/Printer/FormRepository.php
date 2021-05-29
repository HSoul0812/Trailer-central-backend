<?php

namespace App\Repositories\Dms\Printer;

use App\Repositories\Dms\Printer\FormRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Printer\Form;
use App\Models\Region;

class FormRepository implements FormRepositoryInterface {

    private $sortOrders = [
        'name' => [
            'field' => 'name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'name',
            'direction' => 'ASC'
        ],
        'region' => [
            'field' => 'region',
            'direction' => 'DESC'
        ],
        '-region' => [
            'field' => 'region',
            'direction' => 'ASC'
        ],
        'department' => [
            'field' => 'department',
            'direction' => 'DESC'
        ],
        '-department' => [
            'field' => 'department',
            'direction' => 'ASC'
        ],
        'division' => [
            'field' => 'division',
            'direction' => 'DESC'
        ],
        '-division' => [
            'field' => 'department',
            'direction' => 'ASC'
        ]
    ];


    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * Get Form Form
     * 
     * @param array{id: int} $params
     * @throws \Exception when Form form does not exist
     * @return Form
     */
    public function get($params) {
        return Form::findOrFail($params['id']);
    }

    /**
     * Get All Form Forms With Filters
     * 
     * @param array $params
     * @return Collection<Form>
     */
    public function getAll($params) {
        $query = Form::where('id', '>', 0)
                        ->leftJoin(Region::getTableName(), Form::getTableName().'.region', '=', Region::getTableName().'.region_code');
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        // Search Term
        if(isset($params['search_term'])) {
            $query = $query->where(function($query) use($params) {
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
            $query = $query->where(function($query) use($params) {
                $query->where('region', $params['region'])
                      ->orWhere(Region::getTableName() . '.region_name', '=', $params['region']);
            });
        }

        // Find By Department
        if(isset($params['department'])) {
            $query->where('department', $params['department']);
        }

        // Find By Division
        if(isset($params['division'])) {
            $query->where('division', $params['division']);
        }

        // Append Sort
        if (!isset($params['sort'])) {
            $params['sort'] = '-region';
        }
        $query = $this->addSortQuery($query, $params['sort']);
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }


    /**
     * Add Sort Query
     * 
     * @param string $query
     * @param string $sort
     * @return string
     */
    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}