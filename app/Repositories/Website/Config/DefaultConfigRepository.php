<?php

namespace App\Repositories\Website\Config;

use App\Repositories\Website\Config\DefaultConfigRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Website\Config\WebsiteConfigDefault;

class DefaultConfigRepository implements DefaultConfigRepositoryInterface {
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    /**
     * Get All Default Website Config
     * 
     * @param array $params
     * @return Collection<WebsiteConfigDefault>
     */
    public function getAll($params) {
        $query = WebsiteConfigDefault::select('*');
                
        if (isset($params['key'])) {
            $query = $query->where('key', $params['key']);
        }
        
        return $query->get();
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
