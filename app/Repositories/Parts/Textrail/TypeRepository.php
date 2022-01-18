<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Type;
use Illuminate\Support\Facades\DB;

class TypeRepository implements TypeRepositoryInterface
{
    /**     
     * @var App\Models\Parts\Textrail\Type
     */
    protected $model;

    public function __construct(Type $model) {
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

    public function firstOrCreate(array $params) : Type
    {
        return $this->model->firstOrCreate($params);
    }

}