<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Image;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class InventoryImageRepository
 * @package App\Repositories\Inventory
 */
class ImageRepository implements ImageRepositoryInterface
{
    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @return bool
     */
    public function update($params)
    {
        $imageId = $params['id'];

        $image = Image::findOrFail($imageId);

        unset($params['id']);

        return $image->update($params);
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function delete($params)
    {
        $query = Image::query();

        if (isset($params[self::CONDITION_AND_WHERE_IN]) && is_array($params[self::CONDITION_AND_WHERE_IN])) {
            foreach ($params[self::CONDITION_AND_WHERE_IN] as $field => $values) {
                $query = $query->whereIn($field, $values);
            }
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query = $query->where($field, $values);
        }

        return $query->delete();
    }

    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        $query = Image::select('*')
            ->join('inventory_image', 'inventory_image.image_id', '=', 'image.image_id')
            ->join('inventory', 'inventory.inventory_id', '=', 'inventory_image.inventory_id');

        if (!empty($params['inventory_id'])) {
            $query->where('inventory_image.inventory_id', $params['inventory_id']);
        }

        if (isset($params[self::CONDITION_AND_WHERE_IN]) && is_array($params[self::CONDITION_AND_WHERE_IN])) {
            foreach ($params[self::CONDITION_AND_WHERE_IN] as $field => $values) {
                $query->whereIn($field, $values);
            }
        }

        return $query->get();
    }

    /**
     * @param int $inventoryId
     * @param array $params
     * @return Collection
     */
    public function getAllByInventoryId(int $inventoryId, $params = [])
    {
        $query = Image::select('*')
            ->join('inventory_image', 'inventory_image.image_id', '=', 'image.image_id')
            ->join('inventory', 'inventory.inventory_id', '=', 'inventory_image.inventory_id')
            ->where('inventory.inventory_id', $inventoryId);

        if (isset($params[self::RELATION_WITH_COUNT]) && is_string($params[self::RELATION_WITH_COUNT])) {
            $query = $query->withCount($params[self::RELATION_WITH_COUNT]);
        }

        return $query->get();
    }
}
