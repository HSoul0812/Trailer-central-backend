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
        return Entity::where('entity_type', $params['entity_type'])
            ->where('website_id', $params['website_id'])
            ->update(['deleted' =>  1]);
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        return Entity::updateOrCreate(
            [
                'entity_type' => $params['entity_type'],
                'website_id' => $params['website_id']
            ],
            $params
        );
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
