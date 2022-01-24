<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Manufacturer;
use Illuminate\Support\Facades\DB;

class ManufacturerRepository implements ManufacturerRepositoryInterface
{
    /**     
     * @var App\Models\Parts\Textrail\Manufacturer
     */
    protected $model;

    public function __construct(Manufacturer $model) {
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

    public function firstOrCreate(array $params) : Manufacturer
    {
        return $this->model->firstOrCreate($params);
    }

}