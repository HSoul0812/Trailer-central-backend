<?php

namespace App\Repositories\Website;

use App\Repositories\Website\EntityRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Website\Entity;

class EntityRepository implements EntityRepositoryInterface {

    const FILTERS_CONFIG_KEY = 'filters';
    const MANUFACTURER_CONFIG_KEY = 'manufacturer';

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        return Entity::where('entity_type', $params['entity_type'])
            ->where('website_id', $params['website_id'])
            ->update(['deleted' =>  1]);
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

    public function updateConfig($websiteId, array $params) {
        if(isset($params['manufacturers'])) {
            $manufacturers = $params['manufacturers'];
            $query = sprintf('%%"%s"%%', self::MANUFACTURER_CONFIG_KEY);

            $entities = Entity::where('website_id', $websiteId)->where('entity_config', 'like', $query)->get();
            $entities->each(function(Entity $entity) use ($manufacturers) {
                $config = unserialize($entity->entity_config);
                $manufacturersValue = data_get($config, sprintf('%s.%s.*.*', self::FILTERS_CONFIG_KEY, self::MANUFACTURER_CONFIG_KEY));
                $matches = array_intersect($manufacturers, $manufacturersValue);

                if(count($matches) !== count($manufacturersValue)) {
                    unset($config[self::FILTERS_CONFIG_KEY][self::MANUFACTURER_CONFIG_KEY]);
                    $entity->update([
                        'entity_config' => serialize($config)
                    ]);
                }
            });
        }
    }
}
