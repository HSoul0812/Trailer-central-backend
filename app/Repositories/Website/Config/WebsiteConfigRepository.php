<?php

namespace App\Repositories\Website\Config;

use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Website\Config\WebsiteConfig;

class WebsiteConfigRepository implements WebsiteConfigRepositoryInterface {
    
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
        $query = WebsiteConfig::select('*');
                
        if (isset($params['key'])) {
            $query = $query->where('key', $params['key']);
        }
        
        if (isset($params['value'])) {
            $query = $query->where('value', $params['value']);
        }
        
        return $query->get();
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
