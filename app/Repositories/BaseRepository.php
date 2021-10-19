<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class BaseRepository
 *
 * @package App\Repositories
 */
abstract class BaseRepository
{
    /**
     * @var Model|Builder
     */
    protected $model;

    /**
     * Find by id
     *
     * @param int $id
     * @param bool $isTrashed
     * @param array $relation
     * @param array $where
     * @param array $select
     *
     * @return Model|null
     */
    public function findById(
        int $id,
        bool $isTrashed = false,
        array $where = [],
        array $relation = [],
        array $select = ['*']
    ): ?Model {
        $model = $this->model;

        if ($isTrashed) {
            $model = $model->withTrashed();
        }

        if (!empty($select)) {
            $model = $model->select($select);
        }

        if (!empty($where)) {
            $model = $model->where($where);
        }

        if (!empty($relation)) {
            $model = $model->with($relation);
        }

        return $model->find($id);
    }

    /**
     * Create one model
     *
     * @param array $data
     *
     * @return Model
     */
    public function generate(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update Model
     *
     * @param Model $model
     * @param array $data
     * @param bool $isTrashed
     *
     * @return bool
     */
    public function modify(
        Model $model,
        array $data = [],
        bool $isTrashed = false
    ): bool {
        if ($isTrashed) {
            $model->withTrashed();
        }

        return $model->update($data);
    }

    /**
     * Fetch All Data
     *
     * @param array $params
     * @param array $relation
     * @param array $where
     * @param array $whereIn
     *
     * @return Collection|static[]|LengthAwarePaginator
     */
    public function fetchAll(
        array $params = [],
        array $relation = [],
        array $where = [],
        array $whereIn = []
    ) {
        $query = $this->getQuery($params, $relation, $where, $whereIn);

        if (isset($params['limit']) && $params['limit']) {
            return $query->paginate($params['limit']);
        }

        return $query->get();
    }

    /**
     * @param array $params
     * @param array $relation
     * @param array $where
     * @param array $whereIn
     *
     * @return Builder
     */
    protected function getQuery(
        array $params = [],
        array $relation = [],
        array $where = [],
        array $whereIn = []
    ): Builder {
        $query = $this->prepareQueryForGet($params);

        if (!empty($where)) {
            $query = $query->where($where);
        }

        if (!empty($whereIn)) {
            $query = $this->whereInQuery($query, $whereIn);
        }

        if (!empty($relation)) {
            $query = $query->with($relation);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param array $params
     *
     * @return Builder
     */
    protected function whereInQuery(
        Builder $query,
        array $params = []
    ): Builder {
        foreach ($params as $param) {
            if (isset($param['field'], $param['values']) && !empty($param['values']) && $param['field']) {
                $query->whereIn($param['field'], $param['values']);
            }
        }

        return $query;
    }

    /**
     * @param array $params
     * @param Model|Builder $customModel
     *
     * @return Builder
     */
    protected function prepareQueryForGet(array $params, Model $customModel = null): Builder
    {
        $model = $customModel ?? $this->model;

        $query = $model::query();

        $select = $model->getTable() . '.*';

        if (!empty($params['select'])) {
            if (is_array($params['select'])) {
                $select = $params['select'];
            } elseif (is_string($params['select'])) {
                $select = explode(',', $params['select']);
            }
        }

        $query->select($select);

        return $query;
    }

    /**
     * @param array $data
     * @param array $whereCondition
     * @param array $whereInCondition
     *
     * @return void
     */
    public function bulkUpdate(
        array $data = [],
        array $whereCondition = [],
        array $whereInCondition = []
    ): bool {
        $query = $this->model::query();

        if (!empty($whereCondition) && !empty($whereIn)) {
            return false;
        }

        if (!empty($whereCondition)) {
            $query = $query->where($whereCondition);
        }

        if (!empty($whereInCondition)) {
            $query = $this->whereInQuery($query, $whereInCondition);
        }

        return $query->update($data) ? true : false;
    }
}
