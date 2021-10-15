<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Brand;
use Illuminate\Support\Facades\DB;

class BrandRepository implements BrandRepositoryInterface
{
    protected $model;

    public function __construct(Brand $model) {
        $this->model = $model;
    }

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function firstOrCreate(array $params) : Brand
    {
        return $this->model->firstOrCreate($params);
    }

}