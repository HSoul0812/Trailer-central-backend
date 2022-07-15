<?php

namespace App\Repositories\Website\Parts;

use App\Repositories\Website\Parts\FilterRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Filter;

/**
 *
 * @author Eczek
 */
class FilterRepository implements FilterRepositoryInterface {

    const EXCLUDED_ATTRIBUTES = [
        'type',
    ];

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

    public function getAllEcomm() {
        return Filter::where('is_visible', 1)->whereNotIn('attribute', self::EXCLUDED_ATTRIBUTES)->orderBy('position')->get();
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
