<?php

namespace App\Repositories\Website;

use App\Models\Website\Redirect;
use App\Repositories\Website\RedirectRepositoryInterface;
use App\Exceptions\NotImplementedException;

class RedirectRepository implements RedirectRepositoryInterface {
    
    public function create($params) {
        return Redirect::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        $query = Redirect::where('identifier', '>', 0);
        
        foreach($params as $key => $value) {
            $query = $query->where($key, $value);
        }
        
        return $query->firstOrFail();
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
