<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Attribute;

class AttributeRepository implements AttributeRepositoryInterface
{
    protected $model;

    public function __construct(Attribute $model) {
        $this->model = $model;
    }

    public function firstOrCreate(array $params)
    {
        return $this->model->firstOrCreate($params);
    }

    public function create($params)
    {
        // TODO: Implement create() method.
    }

    public function update($params)
    {
        // TODO: Implement update() method.
    }

    public function get($params)
    {
        // TODO: Implement get() method.
    }

    public function delete($params)
    {
        // TODO: Implement delete() method.
    }

    public function getAll($params)
    {
        // TODO: Implement getAll() method.
    }
}
