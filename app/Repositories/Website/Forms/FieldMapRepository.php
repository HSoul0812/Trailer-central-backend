<?php

namespace App\Repositories\Website\Forms;

use App\Exceptions\NotImplementedException;
use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Website\Forms\FieldMap;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class FieldMapRepository
 * @package App\Repositories\Website\Forms
 */
class FieldMapRepository implements FieldMapRepositoryInterface
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
        return FieldMap::findOrFail($params['id']); 
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @param bool $withDefault
     * @return Collection
     */
    public function getAll($params): Collection
    {
        $query = FieldMap::select('*');

        if (isset($params['type'])) {
            $query->where('type', $params['type']);
        }

        return $query->get();
    }

    /**
     * Get All Sorted
     * 
     * @param $params
     * @param bool $withDefault
     * @return Collection
     */
    public function getAllSorted($params): Collection
    {
        // Get All Sorted by Types
        $types = array();
        foreach(FieldMap::MAP_TYPES as $type) {
            $fields = $this->getAll(['type' => $type]);
            $types[$type] = $fields->transform(new FieldMapTransformer());
        }

        // Return Sorted Types Array
        return $types;
    }
}
