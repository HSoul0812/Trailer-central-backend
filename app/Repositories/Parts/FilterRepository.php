<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\FilterRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Filter;

/**
 *  
 * @author Eczek
 */
class FilterRepository implements FilterRepositoryInterface {


    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        return Filter::where('is_visible', 1)->orderBy('position', 'asc')->get();
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
