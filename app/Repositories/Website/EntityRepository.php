<?php

namespace App\Repositories\Website;

use App\Repositories\Website\EntityRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Website\Entity;

class EntityRepository implements EntityRepositoryInterface {
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        if (isset($params['websiteId'])) {
            return Entity::where('website_id', $params['websiteId'])->get();
        }
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }
    
    public function getAllPages($websiteId) {
        return Entity::where('website_id', $websiteId)
                    ->where('is_active', 1)
                    ->where('deleted', 0)
                    ->whereNotIn('entity_view', [Entity::ENTITY_VIEW_HOME, Entity::ENTITY_VIEW_INVENTORY_LIST_SEARCH, Entity::ENTITY_VIEW_LINK, Entity::ENTITY_VIEW_NOT_FOUND])
                    ->where('url_path_external', 0)
                    ->orderBy('sort_order', 'desc')
                    ->get();
        
    }

}
