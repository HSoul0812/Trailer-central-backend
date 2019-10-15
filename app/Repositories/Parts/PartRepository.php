<?php

namespace App\Repositories\Parts;

use App\Repositories\Repository;
use App\Models\Parts\Part;

/**
 *  
 * @author Eczek
 */
class PartRepository implements Repository {
    
    public function create($params) {
        return Part::create($params);
    }

    public function delete($params) {
        $part = Part::findOrFail($params['id']);
        return $part->delete();
    }

    public function get($params) {
        return Part::findOrFail($params['id']);
    }

    public function getAll($params) {
        if (!isset($params['page_size'])) {
            $params['page_size'] = 15;
        }
        
        return Part::paginate($params['page_size']);
    }

    public function update($params) {
        $part = Part::findOrFail($params['id']);
        $part->fill($params);
        if ($part->save()) {
            return $part;
        }       
    }

}
