<?php


namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\File;
use Illuminate\Support\Collection;

/**
 * Class FileRepository
 * @package App\Repositories\Inventory
 */
class FileRepository implements FileRepositoryInterface
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
     * @throws NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
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
        $query = File::query();

        if (isset($params[self::CONDITION_AND_WHERE_IN]) && is_array($params[self::CONDITION_AND_WHERE_IN])) {
            foreach ($params[self::CONDITION_AND_WHERE_IN] as $field => $values) {
                $query = $query->whereIn($field, $values);
            }
        }

        return $query->delete();
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function getAll($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param int $inventoryId
     * @param array $params
     * @return Collection
     */
    public function getAllByInventoryId(int $inventoryId, $params = [])
    {
        $query = File::select('*')
            ->join('inventory_file', 'inventory_file.file_id', '=', 'file.id')
            ->join('inventory', 'inventory.inventory_id', '=', 'inventory_file.inventory_id')
            ->where('inventory.inventory_id', $inventoryId);

        if (isset($params[self::RELATION_WITH_COUNT]) && is_string($params[self::RELATION_WITH_COUNT])) {
            $query = $query->withCount($params[self::RELATION_WITH_COUNT]);
        }

        return $query->get();
    }
}
